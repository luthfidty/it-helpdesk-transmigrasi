<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EvaluasiKategori;
use App\Models\EvaluasiItem;
use App\Models\TepIndikator;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\KawasanTransmigrasi;
use App\Models\KawasanDokumen;
use App\Models\TepNilai;
use App\Models\InfoKawasan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserKawasanController extends Controller
{
    public function dashboardKawasan()
    {
        $user = Auth::user();

        // --- PERBAIKAN: Tambahkan 'tep.indikator' ke dalam with() ---
        // Agar data TEP dan Nama Indikatornya langsung terambil
        $kawasan = KawasanTransmigrasi::with(['provinsi', 'kabupaten', 'tep.indikator'])
                ->find($user->kawasan_id);

        if (!$kawasan) {
            abort(403, 'Anda tidak memiliki kawasan yang terdaftar.');
        }

        // Total nilai TEP (Opsional: Hitung rata-rata atau jumlah nilai TEP)
        // Jika maksudnya total poin TEP:
        $totalTransmigrasi = $kawasan->tep->sum('nilai'); 
        // Atau jika maksudnya jumlah kawasan (tetap 1):
        // $totalTransmigrasi = 1; 

        $totalDokumen = KawasanDokumen::where('kawasan_id', $user->kawasan_id)->count();

        return view('user.dashboard', compact(
            'user',
            'kawasan',
            'totalTransmigrasi',
            'totalDokumen'
        ));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        $user->password = bcrypt($request->password);
        $user->force_change_password = false;
        $user->save();

        return redirect()->route('user.dashboard')
            ->with('success', 'Password berhasil diubah');
    }

    public function DetailDashboard($id)
    {
        // Cegah user buka kawasan milik user lain
        if ($id != auth()->user()->kawasan_id) {
            abort(403, 'Akses ditolak.');
        }

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
    
    public function uploadEval()
    {
        $user = Auth::user();

        // pastikan user punya kawasan
        if (!$user->kawasan_id) {
            abort(403, "User tidak memiliki kawasan.");
        }

        // ambil kawasan + dokumen
        $kawasan = KawasanTransmigrasi::with('dokumen')->findOrFail($user->kawasan_id);

        return view('user.uploadEval', compact('kawasan'));
    }



    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'pdf_file' => 'required|array|min:1',
            'pdf_file.*' => 'required|mimes:pdf|max:10240',
        ]);

        $files = $request->file('pdf_file');
        $count = 0;

        foreach ($files as $file) {

            $newName = time() . '-' . uniqid() . '-' . $file->getClientOriginalName();
            $path = $file->storeAs('dokumen_kawasan', $newName, 'public');

            KawasanDokumen::create([
                'kawasan_id'   => $id,
                'nama_dokumen' => $file->getClientOriginalName(),
                'path_file'    => $path,
            ]);

            $count++;
        }

        return redirect()->route('uploadEval')
            ->with('success', "$count dokumen berhasil diupload!");
    }

    // ===================== PREVIEW =====================
    public function previewFile($id)
    {
        $dok = KawasanDokumen::findOrFail($id);

        if (!Storage::disk('public')->exists($dok->path_file)) {
            abort(404);
        }

        return response()->file(storage_path('app/public/' . $dok->path_file));
    }

    // ===================== UPDATE =====================
    public function updateFile(Request $request, $id)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:5120',
        ]);

        try {
            $dok = KawasanDokumen::findOrFail($id);
            $file = $request->file('pdf_file');

            // delete file lama
            if ($dok->path_file) {
                Storage::disk('public')->delete($dok->path_file);
            }

            // upload baru
            $newName = time() . '-' . uniqid() . '-' . $file->getClientOriginalName();
            $path = $file->storeAs('dokumen_kawasan', $newName, 'public');

            // update db
            $dok->update([
                'nama_dokumen' => $file->getClientOriginalName(),
                'path_file'    => $path,
            ]);

            return back()->with('success', 'PDF berhasil diperbarui!');

        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }
    }

    // ===================== DELETE =====================
    public function deleteFile($id)
    {
        try {
            $dok = KawasanDokumen::findOrFail($id);

            if ($dok->path_file) {
                Storage::disk('public')->delete($dok->path_file);
            }

            $dok->delete();

            return back()->with('success', 'Dokumen berhasil dihapus!');

        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }
    }
    public function getDataKawasan()
    {
        $user = auth()->user();

        $indikator = TepIndikator::orderBy('kode_indikator')->get();
        $kabupaten = Kabupaten::all(); 
        $totalProvinsi = Provinsi::count();
        $totalKabupaten = Kabupaten::count();

        // Daftar lengkap semua kawasan (jika dibutuhkan di halaman)
        $kawasanList = KawasanTransmigrasi::with(['provinsi', 'kabupaten'])
                            ->orderBy('id', 'DESC')
                            ->get();

        // Kawasan khusus user login (single data)
        $kawasan = KawasanTransmigrasi::where('id', $user->kawasan_id)
                ->with(['provinsi', 'kabupaten'])
                ->first();

        $provinsi = Provinsi::all();

        return view('user.dataKawasan', compact(
            'indikator', 'provinsi', 'kawasan', 'kabupaten',
            'totalKabupaten', 'totalProvinsi', 'kawasanList', 'user'
        ));
    }

    public function editDataKawasan($id)
    {
        $item = KawasanTransmigrasi::findOrFail($id);

        $provinsi = Provinsi::all();
        $kabupaten = Kabupaten::where('provinsi_id', $item->provinsi_id)->get();

        return view('/user/dataKawasan', compact('item', 'provinsi', 'kabupaten'));
    }


    public function updateDataKawasan(Request $request, $id)
    {
        // 1. Validasi
        $validator = Validator::make($request->all(), [
            'nama_kawasan'         => 'required|string',
            'kode_kawasan'         => 'required|string|unique:kawasan_transmigrasi,kode_kawasan,' . $id,
            'nama_lokasi'          => 'required|string',
            'kode_lokasi'          => 'required|string',
            'jumlah_desa'          => 'required|integer',
            'jumlah_penduduk'      => 'required|integer',
            
            // Field Opsional
            'intrans'              => 'nullable',
            'produk_unggulan'      => 'nullable|string',
            'potensi'              => 'nullable|string',
            'pendapatan_perkapita' => 'nullable|integer',
            'investasi'            => 'nullable|integer',
            'keg_kolaborasi'       => 'nullable|string',
            
            // Status Desa
            'desa_mandiri'         => 'nullable|integer',
            'desa_maju'            => 'nullable|integer',
            'desa_berkembang'      => 'nullable|integer',
            'desa_tertinggal'      => 'nullable|integer',
            'desa_sangat_tertinggal' => 'nullable|integer',
        ],[
            'kode_kawasan.unique' => 'Maaf, Kode Kawasan tersebut sudah terdaftar.',
            'integer'             => 'Harus berupa angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Data Cleaning (Ubah string kosong "" jadi NULL)
        $data = $request->except(['_token', '_method']); // Buang token

        $kolomAngka = [
            'pendapatan_perkapita', 
            'investasi', 
            'desa_mandiri', 
            'desa_maju', 
            'desa_berkembang', 
            'desa_tertinggal', 
            'desa_sangat_tertinggal'
        ];

        foreach ($kolomAngka as $kolom) {
            if (isset($data[$kolom]) && $data[$kolom] === "") {
                $data[$kolom] = null;
            }
        }

        // 3. Update Database
        try {
            $kawasan = KawasanTransmigrasi::findOrFail($id);
            $kawasan->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data kawasan berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTEP(Request $request, $id)
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

        return redirect()->route('user.dataKawasan')->with('success', 'Data berhasil diperbarui!');
    }

    // profil Kawasan detail
    public function show($id) {
    try {
        // Mengambil data dari tabel info_kawasan beserta relasinya
        // Pastikan model Kabupaten memiliki relasi 'provinsi'
        $data = InfoKawasan::with(['kawasan.kabupaten.provinsi'])->findOrFail($id);
        
        return response()->json($data);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'Data ID ' . $id . ' tidak ditemukan.'], 404);
    } catch (\Exception $e) {
        // Menangkap error relasi 'provinsi' yang tadi muncul
        return response()->json(['error' => 'Masalah Server: ' . $e->getMessage()], 500);
    }
}

    //Delete profil kawasan transmigrasi
    public function destroyProfil($id)
    {
        try {
            // Mencari data berdasarkan ID
            $infoKawasan = InfoKawasan::find($id);

            if (!$infoKawasan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            $infoKawasan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data profil kawasan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
    // public function evaluasiKategori()
    // {
    //     $kategoriList = EvaluasiKategori::with('items')
    //                 ->orderBy('kode_kategori', 'ASC')  
    //                 ->get();

        
    //     return view('/user/evaluasiKategori', compact('kategoriList'));
    // }

    // public function storeStep1(Request $request)
    // {
    //     $request->validate([
    //         'kode_kategori' => 'required',
    //         'nama_kategori' => 'required',
    //     ]);

    //     // Cek apakah kategori sudah ada
    //     $kategori = EvaluasiKategori::where('kode_kategori', $request->kode_kategori)->first();

    //     if ($kategori) {
    //         // Jika kategori sudah ada, tidak buat baru
    //         return response()->json([
    //             'success' => true,
    //             'kategori_id' => $kategori->id,
    //             'kode_kategori' => $kategori->kode_kategori,
    //             'exists' => true
    //         ]);
    //     }

    //     // Jika belum ada → buat baru
    //     $kategori = EvaluasiKategori::create([
    //         'kode_kategori' => $request->kode_kategori,
    //         'nama_kategori' => $request->nama_kategori,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'kategori_id' => $kategori->id,
    //         'kode_kategori' => $kategori->kode_kategori,
    //         'exists' => false
    //     ]);
    // }

    // public function storeStep2(Request $request)
    // {
    //     $request->validate([
    //         'kategori_id' => 'required|integer',
    //         'kode_item'   => 'required|array|min:1',
    //         'nama_item'   => 'required|array|min:1',
    //     ]);

    //     $kategoriId = $request->kategori_id;
    //     $kodeItems  = $request->kode_item;
    //     $namaItems  = $request->nama_item;

    //     // Cek duplikat
    //     if (count($kodeItems) !== count(array_unique($kodeItems))) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Kode indikator tidak boleh duplikat.'
    //         ], 422);
    //     }

    //     $insertData = [];
    //     $now = now();

    //     foreach ($kodeItems as $i => $kode) {
    //         $insertData[] = [
    //             'kategori_id' => $kategoriId,
    //             'kode_item'   => $kode,
    //             'nama_item'   => $namaItems[$i] ?? '',
    //             'created_at'  => $now,
    //             'updated_at'  => $now,
    //         ];
    //     }

    //     EvaluasiItem::insert($insertData);

    //     return response()->json(['success' => true]);
    // }
    // public function getNextKode($kategori_id)
    // {
    //     // Ambil kategori (misalnya F)
    //     $kategori = EvaluasiKategori::find($kategori_id);

    //     if (!$kategori) {
    //         return response()->json(['next' => null]);
    //     }

    //     $prefix = $kategori->kode_kategori; // F

    //     // Ambil item terakhir kategori F, urutkan kode_item
    //     $lastItem = EvaluasiItem::where('kategori_id', $kategori_id)
    //                 ->where('kode_item', 'LIKE', $prefix . '%')
    //                 ->orderBy('kode_item', 'DESC')
    //                 ->first();

    //     if (!$lastItem) {
    //         $next = $prefix . '1';   // Kalau kosong mulai dari F1
    //     } else {
    //         // Ambil angka terakhir
    //         $lastNumber = intval(substr($lastItem->kode_item, strlen($prefix)));
    //         $next = $prefix . ($lastNumber + 1);
    //     }

    //     return response()->json(['next' => $next]);
    // }
    
}
