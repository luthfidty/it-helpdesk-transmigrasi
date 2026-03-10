<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Daftar Evaluasi Kawasan Transmigrasi (TEP)</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo3.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('Logo3.png') }}">
    
    @vite('resources/css/app.css')

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 min-h-screen">
    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="p-6 sm:ml-64 pt-24">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-700">
                Daftar Evaluasi Kawasan Transmigrasi (TEP)
            </h2>
            <div class="flex mb-2">
                <button data-modal-target="tambah-modal" data-modal-toggle="tambah-modal" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mb-4" type="button">
                    Tambah TEP</button>
            </div>  
        </div>

        <!-- Card Wrapper -->
        <div class="bg-white shadow-md rounded-xl p-4">

            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="w-full text-sm text-left text-black-500 dark:text-black-400">

                    <thead class="text-xs text-black-700 uppercase bg-gray-300 dark:bg-gray-700 dark:text-black-400">
                        <tr class="bg-gray-700 text-white text-left">
                            <th class="px-4 py-4 border text-center">No</th>
                            <th class="px-4 py-4 border text-center">Kode Indikator</th>
                            <th class="px-4 py-4 border text-center">Nama Indikator</th>
                            <th class="px-4 py-4 border text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($indikator as $i => $row)
                        <tr class="border-b">
                            <td class="p-3 border">{{ $i + 1 }}</td>
                            <td class="p-3 border">{{ $row->kode_indikator }}</td>
                            <td class="p-3 border">{{ $row->nama_indikator }}</td>
                            <td class="p-3 border text-center flex gap-2 justify-center">

                                <!-- Edit Button -->
                                <button
                                    data-modal-target="edit-modal-{{ $row->id }}"
                                    data-modal-toggle="edit-modal-{{ $row->id }}"
                                    class="flex items-center px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition shadow-sm">
                                    <!-- <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg> -->
                                    Edit
                                </button>

                                <!-- Delete Button -->
                                <button
                                    data-modal-target="delete-modal-{{ $row->id }}"
                                    data-modal-toggle="delete-modal-{{ $row->id }}"
                                    class="flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm">
                                    <!-- <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg> -->
                                    Delete
                                </button>

                            </td>
                        </tr>
                        <div id="tambah-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full">
                            <div class="absolute inset-0 bg-black opacity-50" data-modal-hide="default-modal"></div>
                            <div class="relative p-4 w-full max-w-2xl max-h-full">
                                <!-- Modal content -->
                                <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                                    <!-- Modal header -->
                                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                            Tambah TEP
                                        </h3>
                                        <button type="button" data-modal-hide="tambah-modal"  class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="default-modal">
                                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                            </svg>
                                            <span class="sr-only">Close modal</span>
                                        </button>
                                    </div>
                                    <!-- Modal body -->
                                    <div class="p-4 md:p-5 space-y-4">
                                        <form action="{{ route('storeTEP') }}" method="post" enctype="multipart/form-data">
                                            @csrf
                                                <div class="mb-3">
                                                    <label for="kode_indikator" class="block mb-2 text-m font-medium text-gray-900 dark:text-white">Kode Indikator</label>
                                                    <input type="text" id="kode_indikator" name="kode_indikator" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700" required>
                                                    <p class="mt-2 text-xs text-red-600 dark:text-red-500">@error('kode_indikator') {{ $message }} @enderror</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="nama_indikator" class="block mb-2 text-m font-medium text-gray-900 dark:text-white">Nama Indikator</label>
                                                    <input type="text" id="nama_indikator" name="nama_indikator" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700" required>
                                                    <p class="mt-2 text-xs text-red-600 dark:text-red-500">@error('nama_indikator') {{ $message }} @enderror</p>
                                                </div>
                                                
                                                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                                                    <button type="submit" class="submit-button text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600">Simpan</button>
                                                </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="edit-modal-{{ $row->id }}" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                            <div class="absolute inset-0 bg-black opacity-50"></div>

                            <div class="relative bg-white rounded-lg shadow w-full max-w-lg p-4">
                                <div class="flex justify-between items-center border-b pb-3 mb-3">
                                    <h3 class="text-xl font-semibold">Edit TEP</h3>
                                    <button data-modal-hide="edit-modal-{{ $row->id }}" class="text-gray-600 hover:text-black">✕</button>
                                </div>

                                <form action="{{ route('updateTEP', $row->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label class="block text-sm font-medium">Kode Indikator</label>
                                        <input type="text" name="kode_indikator" value="{{ $row->kode_indikator }}"
                                            class="w-full border rounded p-2" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="block text-sm font-medium">Nama Indikator</label>
                                        <input type="text" name="nama_indikator" value="{{ $row->nama_indikator }}"
                                            class="w-full border rounded p-2" required>
                                    </div>

                                    <div class="flex justify-end mt-4">
                                        <button data-modal-hide="edit-modal-{{ $row->id }}" type="button"
                                                class="mr-2 px-4 py-2 bg-gray-300 rounded">
                                            Batal
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="delete-modal-{{ $row->id }}" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                            <div class="absolute inset-0 bg-black opacity-50"></div>

                            <div class="relative bg-white rounded-lg shadow w-full max-w-md p-5 text-center">
                                <h3 class="text-lg font-semibold mb-4">Hapus TEP?</h3>

                                <p class="text-gray-600">Yakin ingin menghapus indikator <b>{{ $row->kode_indikator }}</b>?</p>

                                <form action="{{ route('deleteTEP', $row->id) }}" method="POST" class="mt-5">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                        Ya, Hapus
                                    </button>

                                    <button type="button"
                                            data-modal-hide="delete-modal-{{ $row->id }}"
                                            class="ml-2 px-4 py-2 bg-gray-300 rounded">
                                        Batal
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 px-4">
                {{ $indikator->links() }}
            </div>
        </div>
    </div>
</body>
</html>
