<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\KawasanTransmigrasi;
use Illuminate\Support\Facades\Hash;

class SesiController extends Controller
{
    // UBAH BAGIAN INI: Tambahkan (Request $request)
    public function welcome(Request $request)
    {
        // 1. Ambil kata kunci pencarian dari input form
        $keyword = $request->input('search');

        // 2. Siapkan Query Dasar
        $query = KawasanTransmigrasi::with(['provinsi', 'kabupaten']);

        // 3. Cek apakah user sedang mencari sesuatu?
        if ($keyword) {
            // Gunakan grouping query (function($q)) agar logika OR tidak merusak query lain
            $query->where(function ($q) use ($keyword) {
                // Cari berdasarkan Nama Kawasan
                $q->where('nama_kawasan', 'LIKE', "%{$keyword}%")
                  
                  // ATAU Cari berdasarkan Nama Provinsi (Relasi)
                  ->orWhereHas('provinsi', function ($p) use ($keyword) {
                      $p->where('nama_provinsi', 'LIKE', "%{$keyword}%");
                  })
                  
                  // ATAU Cari berdasarkan Nama Kabupaten (Relasi)
                  ->orWhereHas('kabupaten', function ($k) use ($keyword) {
                      $k->where('nama_kabupaten', 'LIKE', "%{$keyword}%");
                  });
            });
        }

        // 4. Eksekusi Pagination (Data Tabel)
        $dataKawasan = $query->paginate(10);

        // -----------------------------------------------------------
        
        // 5. Data untuk Peta (Tetap ambil SEMUA agar peta tetap penuh)
        // Catatan: Jika ingin peta ikut terfilter sesuai search, 
        // Anda bisa meng-copy logika "if($keyword)" ke variabel $allLocations juga.
        // Tapi biasanya peta dibiarkan menampilkan semua titik.
        
        $allLocations = KawasanTransmigrasi::whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->with(['provinsi', 'kabupaten'])
                        ->get(['id', 'nama_kawasan', 'latitude', 'longitude', 'provinsi_id', 'kabupaten_id']);

        // Kirim kedua variabel ke View
        return view('welcome', compact('dataKawasan', 'allLocations'));
    }

    // ... (Fungsi-fungsi lain di bawahnya biarkan tetap sama) ...
    
    public function detailKawasan($id)
    {
        $kawasan = KawasanTransmigrasi::with([
            'provinsi',
            'kabupaten',
            'tep.indikator'
        ])->findOrFail($id);

        return response()->json([
            'kawasan' => $kawasan,
            'tep' => $kawasan->tep
        ]);
    }

    public function detailPeta($id) 
    {
        $kawasan = KawasanTransmigrasi::with([
            'provinsi',
            'kabupaten',
            'tep.indikator'
        ])->findOrFail($id);

        return view('detailPeta', compact('kawasan'));
    }

    public function getLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            if ($user->force_change_password) {
                return redirect()->route('user.forcePassword');
            }

            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'admin_biasa' => redirect()->route('admin.dashboard'),
                'user_kawasan' => redirect()->route('user.dashboard'),
            };
        }

        return back()->withErrors(['login' => 'Email atau password salah']);
    }

    function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function getRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'name' => 'required|min:3|max:255',
            'password' => 'required|string|min:6|max:10',
        ]);

        User::create([
            'email' => $validatedData['email'],
            'name' => $validatedData['name'],
            'role' => 'user_kawasan', 
            'password' => Hash::make($validatedData['password']),
            'force_change_password' => 0,
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }

    public function forcePassword()
    {
        return view('user.forcePassword');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed', 
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->force_change_password = 0; 
        $user->save();

        return redirect('/user/dashboard')->with('success', 'Password berhasil diperbarui.');
    }

    public function index()
    {
        $users = User::all();
        $listKawasan = KawasanTransmigrasi::select('id', 'nama_kawasan')->get();
        return view('admin.dataAkun', compact('users', 'listKawasan'));
    }
}