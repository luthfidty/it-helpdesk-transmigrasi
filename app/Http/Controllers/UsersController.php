<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\EvaluasiKategori;
use App\Models\EvaluasiItem;
use App\Models\TepIndikator;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\KawasanTransmigrasi;
use App\Models\KawasanPrioritas;
use App\Models\IndeksInfra;
use App\Models\IndeksEkonomi;
use App\Models\IndeksSosial;
use App\Models\KawasanDokumen;
use App\Models\InfoKawasan;
use App\Models\StatusDesaKawasan;
use App\Models\TepNilai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user(); 

        // Ambil keyword pencarian
        $search = request('search');

        // Statistik
        $totalKawasan = KawasanTransmigrasi::count();
        $totalProvinsi = Provinsi::count();
        $totalKabupaten = Kabupaten::count();

        // Query kawasan + search + PAGINATION
        $kawasanList = KawasanTransmigrasi::with(['provinsi', 'kabupaten'])
                        ->when($search, function ($query, $search) {
                            $query->where('nama_kawasan', 'LIKE', "%{$search}%")
                                ->orWhereHas('provinsi', function ($q) use ($search) {
                                        $q->where('nama_provinsi', 'LIKE', "%{$search}%");
                                })
                                ->orWhereHas('kabupaten', function ($q) use ($search) {
                                        $q->where('nama_kabupaten', 'LIKE', "%{$search}%");
                                });
                        })
                        ->orderBy('id', 'DESC')
                        ->paginate(10) // Menggunakan paginate 10
                        ->withQueryString(); // Agar search tidak hilang saat pindah page

        return view('admin.dashboard', compact(
            'user','totalKawasan','totalProvinsi','totalKabupaten','kawasanList'
        ));
    }
    public function index()
    {
        // PERBAIKAN: Menambahkan 'admin_biasa' ke dalam query agar muncul di list
        $users = User::whereIn('role', ['admin_biasa', 'user_kawasan'])
            ->orderByRaw("CASE role WHEN 'admin_biasa' THEN 1 ELSE 2 END") // Admin biasa di atas
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->fragment('tabel-users');

        return view('admin.resetPassword', compact('users'));
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // PERBAIKAN 1: Proteksi Keamanan
        // Mencegah reset password milik Super Admin
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Gagal! Password Super Admin tidak boleh direset.');
        }

        // PERBAIKAN 2: Set ke Password Default (misal: password123)
        // Agar admin mudah memberitahu user, daripada random string yang susah diingat
        $passwordDefault = 'password123';
        
        $user->password = Hash::make($passwordDefault);
        $user->force_change_password = 1; // Paksa ganti password saat login (opsional)
        $user->save();

        // Kembalikan pesan sukses
        return redirect()->back()->with('success', 'Password untuk ' . $user->name . ' berhasil direset menjadi: ' . $passwordDefault);
    }

    public function getDetailDashboard($id)
    {
        $kawasan = KawasanTransmigrasi::with(['provinsi', 'kabupaten'])->findOrFail($id);

        $tep = TepNilai::with('indikator')
                ->where('kawasan_id', $id)
                ->orderBy('indikator_id')
                ->get();

        return response()->json([
            'kawasan' => $kawasan,
            'tep' => $tep
        ]);
    }
    
    public function evaluasiKategori()
    {
        $kategoriList = EvaluasiKategori::with('items')
                    ->orderBy('kode_kategori', 'ASC')  
                    ->get();

        
        return view('/admin/evaluasiKategori', compact('kategoriList'));
    }

    public function storeStep1(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_kategori' => 'required',
        ]);

        // Cek apakah kategori sudah ada
        $kategori = EvaluasiKategori::where('kode_kategori', $request->kode_kategori)->first();

        if ($kategori) {
            // Jika kategori sudah ada, tidak buat baru
            return response()->json([
                'success' => true,
                'kategori_id' => $kategori->id,
                'kode_kategori' => $kategori->kode_kategori,
                'exists' => true
            ]);
        }

        // Jika belum ada → buat baru
        $kategori = EvaluasiKategori::create([
            'kode_kategori' => $request->kode_kategori,
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json([
            'success' => true,
            'kategori_id' => $kategori->id,
            'kode_kategori' => $kategori->kode_kategori,
            'exists' => false
        ]);
    }

    public function storeStep2(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|integer',
            'kode_item'   => 'required|array|min:1',
            'nama_item'   => 'required|array|min:1',
        ]);

        $kategoriId = $request->kategori_id;
        $kodeItems  = $request->kode_item;
        $namaItems  = $request->nama_item;

        // Cek duplikat
        if (count($kodeItems) !== count(array_unique($kodeItems))) {
            return response()->json([
                'success' => false,
                'message' => 'Kode indikator tidak boleh duplikat.'
            ], 422);
        }

        $insertData = [];
        $now = now();

        foreach ($kodeItems as $i => $kode) {
            $insertData[] = [
                'kategori_id' => $kategoriId,
                'kode_item'   => $kode,
                'nama_item'   => $namaItems[$i] ?? '',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        EvaluasiItem::insert($insertData);

        return response()->json(['success' => true]);
    }
    public function getNextKode($kategori_id)
    {
        // Ambil kategori (misalnya F)
        $kategori = EvaluasiKategori::find($kategori_id);

        if (!$kategori) {
            return response()->json(['next' => null]);
        }

        $prefix = $kategori->kode_kategori; // F

        // Ambil item terakhir kategori F, urutkan kode_item
        $lastItem = EvaluasiItem::where('kategori_id', $kategori_id)
                    ->where('kode_item', 'LIKE', $prefix . '%')
                    ->orderBy('kode_item', 'DESC')
                    ->first();

        if (!$lastItem) {
            $next = $prefix . '1';   // Kalau kosong mulai dari F1
        } else {
            // Ambil angka terakhir
            $lastNumber = intval(substr($lastItem->kode_item, strlen($prefix)));
            $next = $prefix . ($lastNumber + 1);
        }

        return response()->json(['next' => $next]);
    }
    public function getDaftarTEP()
    {
        // Ganti .get() menjadi .paginate(10)
        $indikator = TepIndikator::orderBy('kode_indikator', 'asc')->paginate(10);

        return view('/admin/daftarTEP', compact('indikator'));
    }
    
    public function listTEP(Request $request)
    {
        // 1. Ambil kata kunci pencarian dari input
        $search = $request->input('search');

        // 2. Query Data dengan Filter & Pagination
        $tep = TepIndikator::query()
            ->when($search, function ($query, $search) {
                // Logika pencarian: cari di Nama ATAU Kode
                return $query->where('nama_indikator', 'like', "%{$search}%")
                             ->orWhere('kode_indikator', 'like', "%{$search}%");
            })
            ->orderBy('kode_indikator', 'asc') // Urutkan berdasarkan kode (default)
            ->paginate(10) // Batasi 10 data per halaman
            ->withQueryString(); // Agar saat klik halaman 2, hasil pencarian tidak hilang

        return view('admin.tep', compact('tep'));
    }

    public function storeTEP(Request $request)
    {
        $request->validate([
            'kode_indikator' => 'required|string|max:255|unique:tep_indikator,kode_indikator',
            'nama_indikator' => 'required|string|max:255',
        ]);

        TepIndikator::create([
            'kode_indikator' => $request->kode_indikator,
            'nama_indikator' => $request->nama_indikator,
        ]);

        return back()->with('success', 'Indikator TEP baru berhasil ditambahkan');
    }

    public function updateTEP(Request $request, $id)
    {
        $request->validate([
            'kode_indikator' => 'required',
            'nama_indikator' => 'required'
        ]);

        TepIndikator::findOrFail($id)->update($request->all());

        return redirect()->back()->with('success', 'TEP berhasil diperbarui');
    }

    public function deleteTEP($id)
    {
        TepIndikator::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'TEP berhasil dihapus');
    }

    public function getDataTransmigrasi()
    {
        $indikator = TepIndikator::orderBy('kode_indikator')->get();
        
        $daftarProvinsiMaster = Province::orderBy('name', 'ASC')->get();
        
        $provinsi = Provinsi::all(); 
        $kabupaten = Kabupaten::all();

        $kawasan = KawasanTransmigrasi::with(['provinsi', 'kabupaten'])
                                    // UBAH DARI 'id' MENJADI 'updated_at'
                                    ->orderBy('updated_at', 'desc') 
                                    ->get();

        return view('/admin/dataTransmigrasi', compact(
            'indikator', 
            'daftarProvinsiMaster', 
            'provinsi', 
            'kawasan', 
            'kabupaten'
        ));
    }
    public function getKabupaten($province_id)
    {
        // Mengambil data dari tabel 'regencies' (IndoRegion)
        $data = Regency::where('province_id', $province_id)
                    ->orderBy('name', 'ASC')
                    ->get();
        
        return response()->json($data);
    }
    public function storeDataKawasan(Request $request)
        {
            // 1. Validasi Input
            // Perhatikan: validasi province_id dan regency_id mengarah ke tabel IndoRegion (provinces & regencies)
            $validator = Validator::make($request->all(), [
                'provinsi_id'      => 'required|exists:provinces,id', // Cek ke tabel master provinces
                'kabupaten_id'     => 'required|exists:regencies,id', // Cek ke tabel master regencies
                'nama_kawasan'     => 'required|string',
                'kode_kawasan'     => 'required|string|unique:kawasan_transmigrasi,kode_kawasan',
                'nama_lokasi'      => 'required|string',
                'kode_lokasi'      => 'required|string',
                'jumlah_desa'      => 'required|integer',
                'jumlah_penduduk'  => 'required|integer',
                
                // Opsional
                'intrans'              => 'nullable', 
                'produk_unggulan'      => 'nullable|string',
                'potensi'              => 'nullable|string',
                'pendapatan_perkapita' => 'nullable|integer',
                'investasi'            => 'nullable|integer',
                'keg_kolaborasi'       => 'nullable|string',
                
                // Status Desa
                'desa_mandiri'           => 'nullable|integer',
                'desa_maju'              => 'nullable|integer',
                'desa_berkembang'        => 'nullable|integer',
                'desa_tertinggal'        => 'nullable|integer',
                'desa_sangat_tertinggal' => 'nullable|integer',
                
                'email_pengelola'        => 'nullable|email|unique:users,email', 
                
                // Koordinat (Wajib jika ada di form)
                'latitude'               => 'nullable|string',
                'longitude'              => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                DB::beginTransaction(); // Mulai Transaksi

                // ==========================================================
                // 2. LOGIKA SINKRONISASI WILAYAH (Master -> Custom)
                // ==========================================================
                
                // A. Ambil Data Nama dari Master IndoRegion
                $masterProv = Province::find($request->provinsi_id);
                $masterKab  = Regency::find($request->kabupaten_id);

                // B. Cek/Buat di Tabel Custom Provinsi
                // Logic: Cari provinsi custom berdasarkan nama yang sama persis dengan master.
                // Jika tidak ada, buat baru.
                $customProv = Provinsi::firstOrCreate(
                    ['nama_provinsi' => $masterProv->name] 
                );

                // C. Cek/Buat di Tabel Custom Kabupaten
                // Logic: Cari kabupaten custom berdasarkan nama DAN ID provinsi customnya.
                $customKab = Kabupaten::firstOrCreate(
                    [
                        'nama_kabupaten' => $masterKab->name,
                        'provinsi_id'    => $customProv->id 
                    ]
                );

                // ==========================================================
                // 3. DATA CLEANING & PREPARATION
                // ==========================================================
                
                // Ambil semua data request KECUALI token & password & ID wilayah master
                $data = $request->except(['email_pengelola', 'password_pengelola', '_token', 'provinsi_id', 'kabupaten_id']);

                // GANTI ID wilayah dengan ID dari tabel Custom yang baru disinkronkan
                $data['provinsi_id']  = $customProv->id;
                $data['kabupaten_id'] = $customKab->id;

                // A. Handle Kolom Angka (Default 0 jika kosong)
                $kolomAngkaNotNull = ['pendapatan_perkapita', 'investasi', 'intrans'];
                foreach ($kolomAngkaNotNull as $col) {
                    if (!isset($data[$col]) || $data[$col] === "") {
                        $data[$col] = 0; 
                    }
                }

                // B. Handle Kolom Teks (Default '-' jika kosong)
                $kolomTeksNotNull = ['produk_unggulan', 'keg_kolaborasi', 'potensi'];
                foreach ($kolomTeksNotNull as $col) {
                    if (!isset($data[$col]) || $data[$col] === "") {
                        $data[$col] = "-"; 
                    }
                }

                // C. Handle Kolom Nullable (Set NULL jika kosong)
                $kolomAngkaNullable = [
                    'desa_mandiri', 'desa_maju', 'desa_berkembang', 'desa_tertinggal', 'desa_sangat_tertinggal'
                ];
                foreach ($kolomAngkaNullable as $col) {
                    if (isset($data[$col]) && $data[$col] === "") {
                        $data[$col] = null;
                    }
                }

                // ==========================================================
                // 4. EKSEKUSI SIMPAN
                // ==========================================================
                
                // Simpan Data Kawasan
                $kawasan = KawasanTransmigrasi::create($data);

                // Simpan User Pengelola (Jika ada input email)
                if ($request->filled('email_pengelola')) {
                    $password = $request->filled('password_pengelola') ? $request->password_pengelola : 'password123';

                    User::create([
                        'name'                  => 'User ' . $request->nama_kawasan, 
                        'email'                 => $request->email_pengelola,
                        'password'              => Hash::make($password),
                        'role'                  => 'user_kawasan',
                        'kawasan_id'            => $kawasan->id, 
                        'force_change_password' => 1  
                    ]);
                }

                session()->put('kawasan_id', $kawasan->id); 
                
                DB::commit(); // Commit Transaksi

                return response()->json([
                    'success' => true,
                    'kawasan_id' => $kawasan->id,
                    'message' => 'Berhasil disimpan!'
                ]);

            } catch (\Exception $e) {
                DB::rollBack(); // Rollback jika error
                return response()->json([
                    'success' => false,
                    'message' => 'Database Error: ' . $e->getMessage()
                ], 500);
            }
        }
    public function storeDataTEP(Request $request)
    {
        $validated = $request->validate([
            'indikator_id' => 'required|array',
            'indikator_id.*' => 'integer|exists:tep_indikator,id',
            'nilai' => 'required|array',
            'nilai.*' => 'numeric'
            // 'kawasan_id' tidak diperlukan dalam validasi karena diambil dari session
        ]);

        // Ambil kawasan_id dari session (Sekarang sudah ada karena disimpan di Step 1)
        $kawasan_id = session('kawasan_id'); // <--- Gunakan session

        if (!$kawasan_id) {
            return back()->with('error', 'Data Kawasan tidak ditemukan. Silakan isi Step 1 dulu.');
        }

        // Simpan nilai per indikator
        foreach ($validated['indikator_id'] as $i => $indikatorId) {
            TepNilai::create([
                'kawasan_id' => $kawasan_id,
                'indikator_id' => $indikatorId,
                'nilai' => $validated['nilai'][$i],
            ]);
        }

        // Hapus session setelah data disimpan
        session()->forget('kawasan_id');
        
        // Gunakan route name 'getDataTransmigrasi' untuk redirect
        return redirect() 
            ->route('getDataTransmigrasi')
            ->with('success', 'Data TEP berhasil disimpan!');
    }

    public function getDetail($id)
    {
        // Cek apakah Kawasan Transmigrasi dengan ID tersebut ditemukan
        $kawasan = KawasanTransmigrasi::with(['provinsi', 'kabupaten'])->find($id);

        if (!$kawasan) {
            return response()->json(['message' => 'Data kawasan tidak ditemukan'], 404);
        }

        // Ambil data TEP yang terkait
        $tep = TEPNilai::with('indikator')
            ->where('kawasan_id', $id)
            ->get();

        return response()->json([
            'kawasan' => $kawasan,
            'tep' => $tep
        ]);
    }

    public function editKawasan($id)
    {
        $item = KawasanTransmigrasi::findOrFail($id);

        $provinsi = Provinsi::all();
        $kabupaten = Kabupaten::where('provinsi_id', $item->provinsi_id)->get();

        return view('/admin/dataTransmigrasi', compact('item', 'provinsi', 'kabupaten'));
    }


    public function updateKawasan(Request $request, $id)
    {
        // 1. Validasi Input - Arahkan provinsi_id dan kabupaten_id ke tabel MASTER (provinces/regencies)
        $validator = Validator::make($request->all(), [
            'provinsi_id'      => 'required|exists:provinces,id', // Cek ke tabel master
            'kabupaten_id'     => 'required|exists:regencies,id', // Cek ke tabel master
            'nama_kawasan'     => 'required|string',
            'kode_kawasan'     => 'required|string|unique:kawasan_transmigrasi,kode_kawasan,' . $id,
            'nama_lokasi'      => 'required|string',
            'kode_lokasi'      => 'required|string',
            'jumlah_desa'      => 'required|integer',
            'jumlah_penduduk'  => 'required|integer',
            'intrans'          => 'nullable',
            'produk_unggulan'  => 'nullable|string',
            'potensi'          => 'nullable|string',
            'pendapatan_perkapita' => 'nullable|integer',
            'investasi'        => 'nullable|integer',
            'keg_kolaborasi'   => 'nullable|string',
            'desa_mandiri'     => 'nullable|integer',
            'desa_maju'        => 'nullable|integer',
            'desa_berkembang'  => 'nullable|integer',
            'desa_tertinggal'  => 'nullable|integer',
            'desa_sangat_tertinggal' => 'nullable|integer',
            'email_pengelola'  => 'nullable|email',
        ], [
            'kode_kawasan.unique' => 'Gagal! Kode Kawasan sudah dipakai kawasan lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ==========================================================
            // 2. LOGIKA SINKRONISASI WILAYAH (Master -> Custom)
            // ==========================================================
            $masterProv = Province::find($request->provinsi_id);
            $masterKab  = Regency::find($request->kabupaten_id);

            // Cek/Buat di tabel Custom
            $customProv = Provinsi::firstOrCreate(['nama_provinsi' => $masterProv->name]);
            $customKab  = Kabupaten::firstOrCreate([
                'nama_kabupaten' => $masterKab->name,
                'provinsi_id'    => $customProv->id 
            ]);

            // ==========================================================
            // 3. UPDATE DATA KAWASAN
            // ==========================================================
            $kawasan = KawasanTransmigrasi::findOrFail($id);
            
            // Ambil semua data kecuali field user dan wilayah master
            $data = $request->except(['email_pengelola', '_token', '_method', 'provinsi_id', 'kabupaten_id']);
            
            // Masukkan ID Custom hasil sinkronisasi
            $data['provinsi_id']  = $customProv->id;
            $data['kabupaten_id'] = $customKab->id;

            $kawasan->update($data);

            // ==========================================================
            // 4. LOGIKA UPDATE AKUN USER
            // ==========================================================
            if ($request->filled('email_pengelola')) {
                // Cek email duplikat di user lain
                $emailTerpakai = User::where('email', $request->email_pengelola)
                                    ->where('kawasan_id', '!=', $id)
                                    ->exists();

                if ($emailTerpakai) {
                    return response()->json([
                        'success' => false, 
                        'errors' => ['email_pengelola' => ['Gagal! Email ini sudah digunakan oleh akun lain.']]
                    ], 422);
                }

                // Update atau buat user baru
                $user = User::updateOrCreate(
                    ['kawasan_id' => $id], 
                    [
                        'email' => $request->email_pengelola,
                        'name'  => 'Admin ' . $request->nama_kawasan,
                        'role'  => 'user_kawasan',
                    ]
                );
                
                // Set password jika user baru saja dibuat melalui update ini
                if ($user->wasRecentlyCreated) {
                    $user->password = Hash::make('password123');
                    $user->force_change_password = 1;
                    $user->save();
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data kawasan dan akun pengelola berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateDataTEP(Request $request, $id)
    {
        $kawasan = KawasanTransmigrasi::findOrFail($id);

        // hapus nilai lama
        TepNilai::where('kawasan_id', $id)->delete();

        // simpan nilai baru
        foreach ($request->indikator_id as $i => $indikator_id) {
            TepNilai::create([
                'kawasan_id' => $id,
                'indikator_id' => $indikator_id,
                'nilai' => $request->nilai[$i],
            ]);
        }

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }

    public function deleteKawasan($id)
    {
        try {
            // 1. Cari Data Kawasan
            $kawasan = KawasanTransmigrasi::findOrFail($id);

            // Simpan ID Provinsi & Kabupaten sebelum data dihapus
            $provinsi_id  = $kawasan->provinsi_id;
            $kabupaten_id = $kawasan->kabupaten_id;

            // --- HAPUS USER TERKAIT DULU ---
            User::where('kawasan_id', $id)->delete();

            // 2. Hapus Data Kawasan Utama
            $kawasan->delete();

            // -----------------------------------------------------------
            // LOGIKA PEMBERSIHAN WILAYAH (CLEANUP)
            // -----------------------------------------------------------

            // 3. Cek Kabupaten
            $sisaKawasanDiKab = KawasanTransmigrasi::where('kabupaten_id', $kabupaten_id)->count();
            if ($sisaKawasanDiKab == 0) {
                Kabupaten::destroy($kabupaten_id);
            }

            // 4. Cek Provinsi
            $sisaKawasanDiProv = KawasanTransmigrasi::where('provinsi_id', $provinsi_id)->count();
            if ($sisaKawasanDiProv == 0) {
                Kabupaten::where('provinsi_id', $provinsi_id)->delete();
                Provinsi::destroy($provinsi_id);
            }

            // --- [BAGIAN INI YANG DIGANTI] ---
            // Agar kembali ke halaman sebelumnya, bukan menampilkan JSON hitam putih
            return redirect()->back()->with('success', 'Data kawasan dan user pengelola berhasil dihapus.');

        } catch (\Exception $e) {
            // --- [BAGIAN INI JUGA DIGANTI] ---
            // Agar jika error, user dikembalikan ke halaman dengan pesan error
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function uploadEvaluasi(Request $request)
    {
    
        $search = $request->input('search');

        $kawasan = KawasanTransmigrasi::with('dokumen')
            ->withCount('dokumen') 
            ->when($search, function ($query, $search) {
                $query->where('nama_kawasan', 'like', "%$search%")
                    ->orWhere('nama_lokasi', 'like', "%$search%")
                    ->orWhereHas('provinsi', function ($q) use ($search) {
                        $q->where('nama_provinsi', 'like', "%$search%");
                    })
                    ->orWhereHas('kabupaten', function ($q) use ($search) {
                        $q->where('nama_kabupaten', 'like', "%$search%");
                    });
            })
            ->orderBy('dokumen_count', 'desc') // 1. Prioritas Utama: Paling banyak PDF di atas
            ->orderBy('created_at', 'desc')    // 2. Prioritas Kedua: Data paling baru dibuat di atas
            ->get();
        

        return view('/admin/uploadEvaluasi', compact('kawasan', 'search'));
    }

    public function uploadPDF(Request $request, $id)
            {
            // 1. Validasi Input
            $request->validate([
                'pdf_file' => 'required|array|min:1', 
                // Diubah menjadi 10240 (10MB)
                'pdf_file.*' => 'required|mimes:pdf|max:10240', 
            ], [
                'pdf_file.required' => 'Wajib memilih minimal satu file PDF.',
                'pdf_file.array' => 'File harus diunggah sebagai array.',
                'pdf_file.*.mimes' => 'Hanya file dengan format PDF yang diizinkan.',
                // Pesan error disesuaikan ke 10MB
                'pdf_file.*.max' => 'Ukuran file PDF tidak boleh melebihi 10MB.', 
            ]);

            // Jika validasi lolos, file PASTI ada.
            $files = $request->file('pdf_file');
            $uploadedCount = 0;

            try {
                // 2. Loop File (Kunci Multiple Upload)
                foreach ($files as $file) {
                    
                    // a. Generate nama unik untuk menghindari konflik
                    // Lebih baik menggunakan hash dari konten atau kombinasi yang lebih aman, tapi uniqid() sudah cukup.
                    $fileName = time() . '-' . uniqid() . '-' . $file->getClientOriginalName();

                    // b. Simpan file ke storage (menggunakan disk 'public')
                    $path = $file->storeAs('dokumen_kawasan', $fileName, 'public');

                    // c. Simpan record ke database untuk setiap file
                    KawasanDokumen::create([
                        'kawasan_id'   => $id,
                        'nama_dokumen' => $file->getClientOriginalName(),
                        'path_file'    => $path,
                    ]);
                    
                    $uploadedCount++;
                }
                
                // 3. Respon Sukses
                return back()->with('success', $uploadedCount . ' dokumen berhasil diupload!');
                
            } catch (\Exception $e) {
                // 4. Error Handling
                // Catat error di log Laravel untuk debugging
                Log::error("Gagal Multiple Upload PDF Kawasan ID {$id}: " . $e->getMessage()); 
                
                return back()->with('error', 'Terjadi kesalahan saat mengupload dokumen. Silakan coba lagi atau periksa log.');
            }
        }


        public function previewPDF($id)
        {
            $dok = KawasanDokumen::findOrFail($id);

            if (!$dok->path_file) {
                abort(404);
            }

            return response()->file(storage_path('app/public/' . $dok->path_file));
        }

        public function editPDF($id)
        {
            $dokumen = KawasanDokumen::findOrFail($id);

            // Ambil ulang semua kawasan untuk menampilkan tabel
            $kawasan = KawasanTransmigrasi::with('dokumen')->get();

            return view('admin.uploadEvaluasi', compact('kawasan', 'dokumen'));
        }


        public function updatePDF(Request $request, $id)
        {
            // Validasi 10MB
            $request->validate([
                'pdf_file' => 'required|mimes:pdf|max:10240', 
            ]);

            try {
                $dok = KawasanDokumen::findOrFail($id);
                $fileBaru = $request->file('pdf_file');

                // Hapus file LAMA dari folder storage/app/public/dokumen_kawasan
                if ($dok->path_file && \Storage::disk('public')->exists($dok->path_file)) {
                    \Storage::disk('public')->delete($dok->path_file);
                }

                // Upload file BARU
                $newName = time() . '-' . uniqid() . '-' . $fileBaru->getClientOriginalName();
                $path = $fileBaru->storeAs('dokumen_kawasan', $newName, 'public');

                // Update database
                $dok->update([
                    'nama_dokumen' => $fileBaru->getClientOriginalName(), 
                    'path_file'    => $path,
                ]);

                return back()->with('success', 'PDF berhasil diperbarui!');
                
            } catch (\Exception $e) {
                return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            } 
        }

        public function deletePDF($id)
        {
            try {
                $dok = KawasanDokumen::findOrFail($id);

                // path_file di database berisi 'dokumen_kawasan/nama_file.pdf'
                if ($dok->path_file && Storage::disk('public')->exists($dok->path_file)) {
                    Storage::disk('public')->delete($dok->path_file);
                }

                $dok->delete(); // Menghapus data di database

                return back()->with('success', 'File PDF berhasil dihapus secara permanen!');

            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menghapus file: ' . $e->getMessage());
            }
        }

    function getDaftarAkun()
    {
        $user = User::orderBy('name', 'asc')->get();

        $user = User::orderByRaw("CASE role 
                    WHEN 'admin' THEN 1 
                    WHEN 'admin_biasa' THEN 2 
                    WHEN 'user_kawasan' THEN 3 
                    ELSE 4 END ASC")
                ->orderBy('updated_at', 'desc')
                ->get();

        // dd($user);

        return view('/admin/dataAkun', compact('user'));
    }
    public function storeTambahAkun(Request $request)
    {
        // Validasi form
        $validatedData = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'name' => 'required|min:3|max:255', // Menggunakan 'name' agar konsisten dengan form
            'role' => 'required|in:admin,admin_biasa,user_kawasan',
            'password' => 'required|string|min:6|max:255',
        ]);

        // Simpan data akun
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'password' => Hash::make($validatedData['password']),
        ]);
        
            return response()->json([
            'success' => true,
            'message' => 'Akun berhasil ditambahkan'
        ]);
    }

    public function updateAkun(Request $request, $id) 
    {
        $user = User::findOrFail($id); // Akan melempar 404 jika user tidak ditemukan

        // Validasi input
        $validatedData = $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $id . ',id', // Cek agar email unik kecuali untuk user saat ini
            'name' => 'required|min:3|max:255',
            'role' => 'required|in:admin,admin_biasa,user_kawasan', // Hanya boleh dua opsi ini
            'password' => 'nullable|string|min:6|max:10',
        ]);

        // Update data user
        $user->email = $validatedData['email'];
        $user->name = $validatedData['name'];
        $user->role = $validatedData['role'];

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($validatedData['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Akun berhasil diperbarui');
    }

    function deleteAkun($id)
    {
    $user = User::where('id', $id)->delete();

    return redirect()->back()->with('success', 'Akun berhasil dihapus');
    }

    public function profilKawasan()
    {
        $kawasan = KawasanTransmigrasi::orderBy('nama_kawasan')->get();
        $provinsi = Provinsi::orderBy('nama_provinsi')->get();

        $infoKawasan = InfoKawasan::with('kawasan')
        ->orderBy('created_at', 'desc')
        ->get();

        
        return view('/admin/profilKawasan', compact('kawasan', 'provinsi', 'infoKawasan'));
    }

    public function storeProfil(Request $request)
{
    // 1. Validasi
    $request->validate([
        'kawasan_id' => 'required|exists:kawasan_transmigrasi,id', // Pastikan tabelnya benar
        'latitude'   => 'nullable|numeric',
        'longitude'  => 'nullable|numeric',
    ]);

    DB::transaction(function () use ($request) {

        // A. Update Koordinat di Tabel Induk (KawasanTransmigrasi)
        // Sebelumnya tertulis KawasanPrioritas, saya ubah ke KawasanTransmigrasi agar konsisten
        $kawasan = KawasanTransmigrasi::find($request->kawasan_id);
        
        if ($kawasan) {
            $kawasan->update([
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'produk_unggulan' => $request->produk_unggulan,
                'potensi'         => $request->potensi,
            ]);
        }

        // B. Simpan Data Profil (InfoKawasan)
        // Menggunakan updateOrCreate agar jika diklik simpan 2x tidak error/duplikat
        InfoKawasan::updateOrCreate(
            ['kawasan_id' => $request->kawasan_id], // Kunci pencarian
            [
                'status_kawasan'   => $request->status_kawasan,
                'ipkt_2023'        => $request->ipkt_2023,
                'jumlah_kecamatan' => $request->jumlah_kecamatan,
                'jumlah_desa'      => $request->jumlah_desa,
                'jumlah_sp_bina'   => $request->jumlah_sp_bina,
                'luas_kawasan_ha'  => $request->luas_kawasan_ha,
                'jumlah_penduduk'  => $request->jumlah_penduduk,
                'dasar_penetapan'  => $request->dasar_penetapan ?? '-',
                'kriteria'         => $request->kriteria,
                'potensi_daya_tampung_kk' => $request->potensi_daya_tampung_kk,
            ]
        );

        // C. Simpan Status Desa (Tabel Baru Sesuai Screenshot)
        StatusDesaKawasan::updateOrCreate(
            ['kawasan_id' => $request->kawasan_id], // Cek berdasarkan ID Kawasan
            [
                'desa_mandiri'           => $request->desa_mandiri ?? 0,
                'desa_maju'              => $request->desa_maju ?? 0,
                'desa_berkembang'        => $request->desa_berkembang ?? 0,
                'desa_tertinggal'        => $request->desa_tertinggal ?? 0,
                'desa_sangat_tertinggal' => $request->desa_sangat_tertinggal ?? 0,
            ]
        );
    });

    return redirect()->route('profilKawasan')->with('success', 'Profil kawasan berhasil disimpan');
}

    public function kabupaten($provinsi_id)
    {
        return Kabupaten::where('provinsi_id', $provinsi_id)
            ->orderBy('nama_kabupaten')
            ->get();
    }

    public function kawasan($kabupaten_id)
    {
        return KawasanTransmigrasi::where('kabupaten_id', $kabupaten_id)
            ->orderBy('nama_kawasan')
            ->get();
    }

    public function editProfil($id)
    {
        // 1. Ambil data Info Kawasan beserta relasi ke KawasanTransmigrasi (kawasan)
        $info = InfoKawasan::with(['kawasan.kabupaten.provinsi'])->findOrFail($id);

        // Ubah data info ke array sebagai dasar response
        $response = $info->toArray();

        // 2. BAGIAN YANG DIUBAH:
        // Kita ambil data status desa langsung dari tabel induk (kawasan_transmigrasi)
        // melalui relasi '$info->kawasan'.
        if ($info->kawasan) {
            $response['desa_mandiri']           = $info->kawasan->desa_mandiri ?? 0;
            $response['desa_maju']              = $info->kawasan->desa_maju ?? 0;
            $response['desa_berkembang']        = $info->kawasan->desa_berkembang ?? 0;
            $response['desa_tertinggal']        = $info->kawasan->desa_tertinggal ?? 0;
            $response['desa_sangat_tertinggal'] = $info->kawasan->desa_sangat_tertinggal ?? 0;
            
            // (Opsional) Jika Anda juga ingin mengambil koordinat yang tersimpan di tabel induk
            $response['latitude']  = $info->kawasan->latitude;
            $response['longitude'] = $info->kawasan->longitude;
        }

        return response()->json($response);
    }

    public function updateProfil(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'kawasan_id' => 'required',
        ]);

        DB::transaction(function () use ($request, $id) {
            // 1. Update Tabel Info Kawasan (Child)
            $info = InfoKawasan::findOrFail($id);
            $info->update([
                'kawasan_id'              => $request->kawasan_id,
                'status_kawasan'          => $request->status_kawasan,
                'ipkt_2023'               => $request->ipkt_2023,
                'jumlah_kecamatan'        => $request->jumlah_kecamatan,
                'jumlah_desa'             => $request->jumlah_desa,
                'jumlah_sp_bina'          => $request->jumlah_sp_bina,
                'luas_kawasan_ha'         => $request->luas_kawasan_ha,
                'jumlah_penduduk'         => $request->jumlah_penduduk,
                'potensi_daya_tampung_kk' => $request->potensi_daya_tampung_kk,
                'dasar_penetapan'         => $request->dasar_penetapan,
                'kriteria'                => $request->kriteria,
            ]);

            // 2. Update Tabel Utama KAWASAN TRANSMIGRASI (Parent)
            // Ini yang akan mengisi kolom NULL di screenshot phpMyAdmin Anda
            $kawasan = KawasanTransmigrasi::find($request->kawasan_id);
            
            if ($kawasan) {
                $kawasan->update([
                    'desa_mandiri'           => $request->desa_mandiri,
                    'desa_maju'              => $request->desa_maju,
                    'desa_berkembang'        => $request->desa_berkembang,
                    'desa_tertinggal'        => $request->desa_tertinggal,
                    'desa_sangat_tertinggal' => $request->desa_sangat_tertinggal,
                    
                    // Jangan lupa update koordinat jika di form ada input ini
                    'latitude'               => $request->latitude,
                    'longitude'              => $request->longitude,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Profil kawasan berhasil diperbarui');
    }
    public function kawasanPrioritas()
    {
        $provinsi = Provinsi::all();
        $infoKawasan = KawasanPrioritas::with(['provinsi', 'kabupaten'])
        ->paginate(10)
        ->fragment('tabel-kawasan');

        /* ================= SUMMARY ================= */
        $totalKawasan = KawasanPrioritas::count();
        $totalProvinsi = KawasanPrioritas::distinct('provinsi_id')->count('provinsi_id');

        /* ================= TOP 10 KAWASAN (FIXED) ================= */
        $topKawasan = KawasanPrioritas::with(['indeksInfra', 'indeksSosial', 'indeksEkonomi'])
            ->get()
            ->map(function ($k) {
                $valInfra = optional($k->indeksInfra)->indeks_infrastruktur ?? 0;
                $valSosial = optional($k->indeksSosial)->indeks_sosial ?? 0;
                $valEkonomi = optional($k->indeksEkonomi)->indeks_ekonomi ?? 0;

                return [
                    'nama_kawasan'   => $k->nama_kawasan,
                    'indeks_infra'   => $valInfra,     
                    'indeks_sosial'  => $valSosial,
                    'indeks_ekonomi' => $valEkonomi,
                    'total_skor'     => $valInfra + $valSosial + $valEkonomi
                ];
            })
            ->sortByDesc('total_skor')
            ->take(10)
            ->values();

        /* ================= FUNNEL PROVINSI (UPDATED) ================= */
        // MENGGUNAKAN QUERY SQL AGAR LEBIH CEPAT & FORMAT SESUAI HIGHCHARTS
        $funnelProvinsi = KawasanPrioritas::join('provinsi', 'kawasan_prioritas.provinsi_id', '=', 'provinsi.id')
            ->select('provinsi.nama_provinsi', DB::raw('count(*) as total'))
            ->groupBy('provinsi.nama_provinsi')
            ->orderByDesc('total')
            ->limit(10) // Ambil top 10 provinsi terbanyak saja agar chart rapi
            ->get()
            ->map(function ($item) {
                // Ubah format object menjadi array index: ['Jawa Barat', 5]
                return [$item->nama_provinsi, $item->total];
            });

        /* ================= MAP DATA ================= */
        $mapData = KawasanPrioritas::with(['provinsi', 'indeksInfra', 'indeksSosial', 'indeksEkonomi'])
            ->get()
            ->map(function ($k) {
                $valInfra = optional($k->indeksInfra)->indeks_infrastruktur ?? 0;
                $valSosial = optional($k->indeksSosial)->indeks_sosial ?? 0;
                $valEkonomi = optional($k->indeksEkonomi)->indeks_ekonomi ?? 0;

                return [
                    'name'    => $k->nama_kawasan,
                    'prov'    => $k->provinsi->nama_provinsi ?? '-',
                    // Pastikan kolom latitude/longitude ada di tabel kawasan_prioritas
                    'lat'     => $k->latitude ?? -2.5489, 
                    'lng'     => $k->longitude ?? 118.0149, 
                    'infra'   => $valInfra,
                    'sosial'  => $valSosial,
                    'ekonomi' => $valEkonomi,
                ];
            });

        /* ================= MAP DATA & CHART SIDEBAR ================= */
        // Kita ambil semua data kawasan beserta nilai indeksnya
        $mapData = KawasanPrioritas::with(['provinsi', 'indeksInfra', 'indeksSosial', 'indeksEkonomi'])
            ->get()
            ->map(function ($k) {
                // Ambil nilai, jika null (belum diinput) set ke 0
                $valInfra   = floatval($k->indeksInfra->indeks_infrastruktur ?? 0);
                $valSosial  = floatval($k->indeksSosial->indeks_sosial ?? 0);
                $valEkonomi = floatval($k->indeksEkonomi->indeks_ekonomi ?? 0);

                return [
                    // Identitas
                    'id'      => $k->id, // Penting untuk unique key
                    'name'    => $k->nama_kawasan,
                    'prov'    => $k->provinsi->nama_provinsi ?? 'Belum ada Provinsi',
                    
                    // Koordinat (Pastikan di tabel kawasan_prioritas ada kolom latitude & longitude)
                    // Jika null, kita kasih default di tengah Indonesia agar map tidak error
                    'lat'     => $k->kawasanTransmigrasi->latitude ?? -2.5489, 
                    'lng'     => $k->kawasanTransmigrasi->longitude ?? 118.0149, 

                    // Data Indeks (Sesuai request)
                    'infra'   => $valInfra,
                    'sosial'  => $valSosial,
                    'ekonomi' => $valEkonomi,
                    
                    // Total (Jika nanti butuh sorting berdasarkan total)
                    'total'   => $valInfra + $valSosial + $valEkonomi
                ];
            });

        return view('admin.kawasanPrioritas', compact(
            'provinsi',
            'infoKawasan',
            'totalKawasan',
            'totalProvinsi',
            'topKawasan',
            'funnelProvinsi',
            'mapData'
        ));
    }

    public function storeKawasanPrioritas(Request $request)
    {
        $request->validate([
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_id' => 'required|exists:kabupaten,id',
            'nama_kawasan' => 'required|string|max:255',
        ]);

        KawasanPrioritas::create([
            'provinsi_id' => $request->provinsi_id,
            'kabupaten_id' => $request->kabupaten_id,
            'nama_kawasan' => $request->nama_kawasan,
        ]);

        return redirect()->back()->with('success', 'Data kawasan prioritas berhasil disimpan');
    }
    public function indeksInfra()
    {
        /* ================= KPI ================= */
        $kpi = [
            'totalKawasan' => IndeksInfra::count(),
            'avgIndeks' => round(IndeksInfra::avg('indeks_infrastruktur'), 2),
            'avgTransportasi' => round(IndeksInfra::avg('transportasi'), 2),
            'avgTelekomunikasi' => round(IndeksInfra::avg('telekomunikasi'), 2),
            'avgAirBersih' => round(IndeksInfra::avg('air_bersih'), 2),
            'avgKesehatan' => round(IndeksInfra::avg('kesehatan'), 2),
            'avgListrik' => round(IndeksInfra::avg('listrik'), 2),
            'avgSarprasKomoditas' => round(IndeksInfra::avg('sarpras_komoditas'), 2),
        ];

        /* ================= RADAR ================= */
        $avgKomponen = IndeksInfra::selectRaw('
            AVG(transportasi) as transportasi,
            AVG(telekomunikasi) as telekomunikasi,
            AVG(air_bersih) as air_bersih,
            AVG(kesehatan) as kesehatan,
            AVG(listrik) as listrik,
            AVG(sarpras_komoditas) as sarpras
        ')->first();

        /* ================= BAR: Indeks per Kawasan ================= */
        $indeksPerKawasan = IndeksInfra::with('kawasan')
            ->select(
                'kawasan_prioritas_id',
                DB::raw('AVG(indeks_infrastruktur) as nilai')
            )
            ->groupBy('kawasan_prioritas_id')
            ->orderByDesc('nilai')
            ->take(10)
            ->get()
            ->map(fn ($i) => [
                'kawasan' => $i->kawasan->nama_kawasan ?? '-',
                'nilai' => round($i->nilai, 2),
            ]);

        /* ================= SCATTER ================= */
        $scatter = IndeksInfra::get()->map(fn ($i) => [
            'x' => (float) $i->air_bersih,
            'y' => (float) $i->kesehatan,
        ]);

        /* ================= TABEL ================= */
        $rekap = IndeksInfra::with('kawasan')
            ->orderByDesc('indeks_infrastruktur')
            ->paginate(10)
            ->fragment('tabel-rekap');

        return view('admin.indeksInfra', compact(
            'kpi',
            'avgKomponen',
            'indeksPerKawasan',
            'scatter',
            'rekap'
        ));
    }

    public function dataIndeksInfra()
    {   
        $indeksInfra = IndeksInfra::with('kawasan.provinsi')->get();
        $kawasan = KawasanPrioritas::with('provinsi')->get();

        // === DATA TRANSPORTASI BY PROVINSI ===
        $transportasiByProvinsi = DB::table('indeks_infra')
            ->join('kawasan_prioritas', 'indeks_infra.kawasan_prioritas_id', '=', 'kawasan_prioritas.id')
            ->join('provinsi', 'kawasan_prioritas.provinsi_id', '=', 'provinsi.id')
            ->select(
                'provinsi.nama_provinsi',
                DB::raw('AVG(indeks_infra.transportasi) as rata_transportasi')
            )
            ->groupBy('provinsi.nama_provinsi')
            ->orderBy('provinsi.nama_provinsi')
            ->get();

        return view('admin.dataIndeksInfra', compact('indeksInfra','kawasan','transportasiByProvinsi'));
    }

    public function storeIndeksInfra(Request $request)
    {
        $request->validate([
            'kawasan_prioritas_id' => 'required|exists:kawasan_prioritas,id',
            'indeks_infrastruktur' => 'required|numeric',
            'transportasi' => 'required|numeric',
            'telekomunikasi' => 'required|numeric',
            'air_bersih' => 'required|numeric',
            'kesehatan' => 'required|numeric',
            'listrik' => 'required|numeric',
            'sarpras_komoditas' => 'required|numeric',
        ]);

        IndeksInfra::create([
            'kawasan_prioritas_id' => $request->kawasan_prioritas_id,
            'indeks_infrastruktur' => $request->indeks_infrastruktur,
            'transportasi' => $request->transportasi,
            'telekomunikasi' => $request->telekomunikasi,
            'air_bersih' => $request->air_bersih,
            'kesehatan' => $request->kesehatan,
            'listrik' => $request->listrik,
            'sarpras_komoditas' => $request->sarpras_komoditas,
        ]);

        return redirect()->route('dataIndeksInfra')->with('success', 'Data indeks infrastruktur berhasil disimpan');
    }
    public function indeksEkonomi()
    {   
        $data = IndeksEkonomi::with('kawasan.provinsi')->get();

        // Rekap
        $totalKawasan = $data->count();
        $avgIndeks    = round($data->avg('indeks_ekonomi'), 2);
        $avgBumdes    = round($data->avg('bumdes'), 2);
        $avgHwTrans   = round($data->avg('hw_trans'), 2);
        $avgKoperasi  = round($data->avg('koperasi'), 2);
        
        // Grafik per Provinsi
        $chartProvinsi = IndeksEkonomi::with('kawasan.provinsi')
            ->get()
            ->groupBy(fn ($i) => $i->kawasan?->provinsi?->nama_provinsi ?? 'Lainnya')
            ->map(fn ($row) => round($row->avg('indeks_ekonomi'), 2))
            ->sortDesc()      // urutkan dari tertinggi
            ->take(10);       // ambil 10 teratas

        $chartKawasan = IndeksEkonomi::with('kawasan.provinsi')
            ->get()
            ->groupBy(fn ($i) => $i->kawasan?->provinsi?->nama_provinsi ?? 'Lainnya')
            ->map(function ($rows, $provinsi) {
                return [
                    'provinsi' => $provinsi,
                    'indeks'    => round($rows->avg('indeks_ekonomi'), 2),
                    'bumdes'   => round($rows->avg('bumdes'), 2),
                    'koperasi' => round($rows->sum('koperasi'), 2),
                ];
            })
            ->sortByDesc('indeks')
            ->take(10)
            ->values(); 
        
        $rekap = IndeksEkonomi::with('kawasan.provinsi')
        ->paginate(10)
        ->fragment('tabel-rekap');

        return view('admin.indeksEkonomi', compact(
            'totalKawasan',
            'avgIndeks',
            'avgBumdes',
            'avgHwTrans',
            'avgKoperasi',
            'chartKawasan',
            'chartProvinsi', 
            'rekap'
        ));
            
    }

    public function dataIndeksEkonomi()
    {   
        $indeksEkonomi = IndeksEkonomi::with('kawasan.provinsi')->get();
        $kawasan = KawasanPrioritas::with('provinsi')->get();
        
        return view('admin.dataIndeksEkonomi', compact('indeksEkonomi','kawasan'));
    }

    public function storeIndeksEkonomi(Request $request)
    {
        $request->validate([
            'kawasan_prioritas_id' => 'required|exists:kawasan_prioritas,id',
            'indeks_ekonomi' => 'required|numeric',
            'bumdes' => 'required|numeric',
            'hw_trans' => 'required|numeric',
            'koperasi' => 'required|numeric',
        ]);

        IndeksEkonomi::create([
            'kawasan_prioritas_id' => $request->kawasan_prioritas_id,
            'indeks_ekonomi' => $request->indeks_ekonomi,
            'bumdes' => $request->bumdes,
            'hw_trans' => $request->hw_trans,
            'koperasi' => $request->koperasi,
        ]);

        return redirect()->route('dataIndeksEkonomi')->with('success', 'Data indeks ekonomi berhasil disimpan');
    }

    public function indeksSosial()
    {   
        $kpi = [
            'totalKawasan' => IndeksSosial::count(),
            'avgIndeks' => IndeksSosial::avg('indeks_sosial'),
            'avgLembaga' => IndeksSosial::avg('lembaga'),
            'avgPemberdayaan' => IndeksSosial::avg('pemberdayaan'),
            'sumGapoktan' => IndeksSosial::sum('gapoktan'),
            'sumPokdarwis' => IndeksSosial::sum('pokdarwis'),
            'sumPokdakan' => IndeksSosial::sum('pokdakan'),
            'sumPoklahsar' => IndeksSosial::sum('poklahsar'),
        ];

        // Grafik Indeks per Kawasan
        $indeksPerKawasan = IndeksSosial::with('kawasan')
            ->orderByDesc('indeks_sosial')
            ->take(10)
            ->get()
            ->map(fn ($i) => [
                'kawasan' => $i->kawasan->nama_kawasan,
                'nilai'   => round($i->indeks_sosial, 2),
            ]);


        // Radar indikator
        $scatter = IndeksSosial::with('kawasan')
            ->get()
            ->map(fn ($i) => [
                'x' => round($i->lembaga, 2),   // sumbu X
                'y' => (int) $i->gapoktan,      // sumbu Y
                'kawasan' => $i->kawasan->nama_kawasan,
            ]);


        // Kelembagaan
        $kelembagaanPerKawasan = IndeksSosial::with('kawasan')
            ->whereHas('kawasan') 
            ->orderByDesc('indeks_sosial')
            ->select(
                'kawasan_prioritas_id', 
                'gapoktan',
                'pokdarwis',
                'pokdakan',
                'poklahsar'
            )
            ->limit(10)
            ->get()
            ->map(fn ($i) => [
                'kawasan'    => $i->kawasan->nama_kawasan,
                'gapoktan'   => (int) $i->gapoktan,
                'pokdarwis'  => (int) $i->pokdarwis,
                'pokdakan'   => (int) $i->pokdakan,
                'poklahsar'  => (int) $i->poklahsar,
            ]);


        // Tabel Rekap
        $rekap = IndeksSosial::with('kawasan.provinsi')
        ->paginate(10)
        ->fragment('tabel-rekap');

        return view('admin.indeksSosial', compact('kpi','indeksPerKawasan','scatter','kelembagaanPerKawasan', 'rekap'));
    }

    public function dataIndeksSosial()
    {   
        $indeksSosial = IndeksSosial::with('kawasan.provinsi')->get();
        $kawasan = KawasanPrioritas::with('provinsi')->get();
        
        return view('admin.dataIndeksSosial', compact('indeksSosial','kawasan'));
    }

    public function storeIndeksSosial(Request $request)
    {
        $request->validate([
            'kawasan_prioritas_id' => 'required|exists:kawasan_prioritas,id',
            'indeks_sosial' => 'required|numeric',
            'lembaga' => 'required|numeric',
            'pemberdayaan' => 'required|numeric',
            'gapoktan' => 'required|integer',
            'pokdarwis' => 'required|integer',
            'pokdakan' => 'required|integer',
            'poklahsar' => 'required|integer',
        ]);

        IndeksSosial::create([
            'kawasan_prioritas_id' => $request->kawasan_prioritas_id,
            'indeks_sosial' => $request->indeks_sosial,
            'lembaga' => $request->lembaga,
            'pemberdayaan' => $request->pemberdayaan,
            'gapoktan' => $request->gapoktan,
            'pokdarwis' => $request->pokdarwis,
            'pokdakan' => $request->pokdakan,
            'poklahsar' => $request->poklahsar,
        ]);

        return redirect()->route('dataIndeksSosial')->with('success', 'Data indeks sosial berhasil disimpan');
    }
    

    
}