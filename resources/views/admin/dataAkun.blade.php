<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Data Akun</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo3.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('Logo3.png') }}">
    
    @vite('resources/css/app.css') 
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
@include('layouts.header')
@include('layouts.sidebar')

<div class="p-4 sm:ml-64 pt-16">
    <div class="items-center justify-between lg:flex">
        <div class="p-2 w-full">

            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <h2 class="text-2xl font-bold text-gray-700">
                    Manajemen Data Akun
                </h2>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        data-modal-target="tambah-modal"
                        data-modal-toggle="tambah-modal"
                        class="px-5 py-2.5 text-sm font-medium text-white
                            bg-blue-600 rounded-lg shadow-sm
                            hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300
                            transition">
                        Tambah Akun
                    </button>

                    <a href="{{ route('admin.users') }}"
                    class="px-5 py-2.5 text-sm font-medium text-white
                            bg-orange-500 rounded-lg shadow-sm
                            hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-300
                            transition">
                        Reset Password
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="w-full text-sm text-left text-black-500 dark:text-black-400">
                    <thead class="text-xs text-black-700 uppercase bg-gray-300 dark:bg-gray-700 dark:text-black-400">
                        <tr class="bg-gray-700 text-white text-left">
                            <th class="px-4 py-4 border">No</th>
                            <th class="px-4 py-3 border">Nama Lengkap</th>
                            <th class="px-4 py-3 border">Email</th>
                            <th class="px-4 py-3 border">Role</th>
                            <th class="px-4 py-3 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="table-body-user">
                        @foreach ($user as $u)
                        <tr class="border-b user-row">
                            <td class="px-6 py-4 border row-number">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 border">{{ $u->name }}</td>
                            <td class="px-6 py-4 border">{{ $u->email }}</td>
                            
                            <td class="px-6 py-4 border">
                                @if ($u->role == 'admin')
                                    <span class="bg-purple-100 text-purple-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-purple-400">Super Admin</span>
                                @elseif ($u->role == 'admin_biasa')
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-blue-400">Admin</span>
                                @elseif ($u->role == 'user_kawasan')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-green-400">User Kawasan</span>
                                @else
                                    {{ $u->role }}
                                @endif
                            </td>

                            <td class="px-6 py-4 border">
                                <div class="flex items-center space-x-4">
                                    <button type="button"
                                        data-modal-target="edit-modal{{ $u->id }}"
                                        data-modal-toggle="edit-modal{{ $u->id }}"
                                        class="flex items-center px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition shadow-sm">
                                        Edit
                                    </button>

                                    <button type="button"
                                        data-modal-target="delete-modal{{ $u->id }}"
                                        data-modal-toggle="delete-modal{{ $u->id }}"
                                        class="flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div id="edit-modal{{ $u->id }}" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
                            class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">
                            <div class="relative w-full max-w-md mx-auto mt-20">
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Edit Akun</h3>

                                    <form action="{{ route('updateAkun', $u->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium">Nama Lengkap</label>
                                            <input type="text" name="name" value="{{ $u->name }}" 
                                                class="w-full border-gray-300 rounded-lg" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium">Email</label>
                                            <input type="email" name="email" value="{{ $u->email }}" 
                                                class="w-full border-gray-300 rounded-lg" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                            <div class="relative">
                                                <select name="role" class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition" required>
                                                    <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>Super Admin</option>
                                                    <option value="admin_biasa" {{ $u->role == 'admin_biasa' ? 'selected' : '' }}>Admin</option>
                                                    <option value="user_kawasan" {{ $u->role == 'user_kawasan' ? 'selected' : '' }}>User Kawasan</option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium">Password (Opsional)</label>
                                            <input type="password" name="password" 
                                                class="w-full border-gray-300 rounded-lg" placeholder="Biarkan kosong jika tidak diganti">
                                        </div>

                                        <div class="flex justify-end space-x-3">
                                            <button data-modal-hide="edit-modal{{ $u->id }}" type="button" 
                                                class="px-4 py-2 bg-gray-300 rounded-lg">Batal</button>
                                            <button class="px-4 py-2 bg-yellow-700 text-white rounded-lg">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div id="delete-modal{{ $u->id }}" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
                            class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">
                            <div class="flex items-center justify-center min-h-screen px-4">
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
                                    <div class="mx-auto mb-4 flex items-center justify-center w-16 h-16 rounded-full bg-red-100">
                                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M10.29 3.86L2.82 16.14A2 2 0 004.64 19h14.72a2 2 0 001.82-2.86L13.71 3.86a2 2 0 00-3.42 0z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-semibold text-gray-800 text-center mb-2">Apakah Anda yakin hapus akun ini?</h2>
                                    <form action="{{ route('deleteAkun', $u->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="flex justify-center space-x-3">
                                            <button type="button" data-modal-hide="delete-modal{{ $u->id }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button>
                                            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="pagination-controls" class="flex justify-end items-center mt-4 gap-2"></div>

        </div>
    </div>
</div>

<div id="tambah-modal" tabindex="-1" aria-hidden="true" data-modal-backdrop="static" 
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">
    <div class="relative w-full max-w-md mx-auto mt-20">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Akun</h3>
            <div id="error-container" class="hidden p-3 mb-3 text-red-700 bg-red-100 rounded">
                <ul id="error-list"></ul>
            </div>

            <form id="form-tambah-akun" action="{{ route('storeTambahAkun') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium">Nama Lengkap</label>
                    <input type="text" name="name" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Password</label>
                    <input type="password" name="password" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Role</label>
                    <select name="role" class="w-full border-gray-300 rounded-lg" required>
                        <option value="admin">Super Admin</option>
                        <option value="admin_biasa">Admin</option>
                        <option value="user_kawasan">User Kawasan</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button data-modal-hide="tambah-modal" type="button" class="px-4 py-2 bg-gray-300 rounded-lg">Batal</button>
                    <button class="px-4 py-2 bg-slate-800 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // --- PAGINATION LOGIC ---
        const rowsPerPage = 10;
        let currentPage = 1;
        
        const paginationContainer = document.getElementById('pagination-controls');
        const allRows = Array.from(document.querySelectorAll('#table-body-user > tr')); // Ambil TR langsung anak tbody

        function updateTable() {
            const totalPages = Math.ceil(allRows.length / rowsPerPage);
            
            if (currentPage > totalPages) currentPage = 1;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            // Sembunyikan semua baris
            allRows.forEach(row => row.style.display = 'none');

            // Tampilkan baris untuk halaman ini
            const rowsToShow = allRows.slice(start, end);
            
            rowsToShow.forEach((row, index) => {
                row.style.display = ''; // Tampilkan
                // Update Nomor Urut
                const cellNo = row.querySelector('.row-number');
                if(cellNo) {
                    cellNo.innerText = start + index + 1;
                }
            });

            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            paginationContainer.innerHTML = ''; 

            if (totalPages <= 1) return;

            // Tombol Previous
            const prevBtn = createPageButton('<', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                }
            }, currentPage === 1);
            paginationContainer.appendChild(prevBtn);

            // Tombol Angka Logic (Max 5 button)
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);

            if (endPage - startPage < 4) {
                if (startPage === 1) endPage = Math.min(totalPages, startPage + 4);
                else if (endPage === totalPages) startPage = Math.max(1, endPage - 4);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = createPageButton(i, () => {
                    currentPage = i;
                    updateTable();
                }, false, i === currentPage);
                paginationContainer.appendChild(pageBtn);
            }

            // Tombol Next
            const nextBtn = createPageButton('>', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                }
            }, currentPage === totalPages);
            paginationContainer.appendChild(nextBtn);
        }

        function createPageButton(text, onClick, disabled = false, isActive = false) {
            const btn = document.createElement('button');
            btn.innerText = text;
            btn.disabled = disabled;
            
            let classes = "px-3 py-1 border rounded text-sm transition ";
            if (isActive) {
                classes += "bg-slate-800 text-white border-slate-800";
            } else if (disabled) {
                classes += "bg-gray-100 text-gray-400 cursor-not-allowed";
            } else {
                classes += "bg-white text-gray-700 hover:bg-gray-100";
            }
            
            btn.className = classes;
            btn.addEventListener('click', onClick);
            return btn;
        }

        // Init Table
        updateTable();
    });

    // --- AJAX TAMBAH AKUN ---
    document.getElementById('form-tambah-akun').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const errorContainer = document.getElementById('error-container'); 
        const errorList = document.getElementById('error-list');

        const tokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!tokenElement) {
            alert("Error: CSRF Token tidak ditemukan.");
            return;
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': tokenElement.getAttribute('content')
            }
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                window.location.reload();
            } else {
                errorList.innerHTML = '';
                if (data.errors) {
                    Object.values(data.errors).forEach(err => {
                        errorList.innerHTML += `<li>${err[0]}</li>`;
                    });
                } else {
                    errorList.innerHTML += `<li>${data.message || 'Terjadi kesalahan sistem.'}</li>`;
                }
                errorContainer.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert("Gagal menghubungi server.");
        });
    });
</script>

</body>
</html>