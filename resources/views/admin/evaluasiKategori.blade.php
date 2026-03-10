<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Kawasan Transmigrasi</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo3.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('Logo3.png') }}">
    
    @vite('resources/css/app.css') 
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-gray-100 min-h-screen">
    @include('layouts.header')
    @include('layouts.sidebar')
    <div class="p-6 sm:ml-64 mt-20">
         <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-700">Daftar Evaluasi Kawasan</h2>

                <button data-modal-target="modal-evaluasi" data-modal-toggle="modal-evaluasi"
                    class="px-4 py-2 bg-blue-700 text-white rounded-lg shadow hover:bg-blue-800">
                    Tambah Evaluasi Kawasan
                </button>

            </div>

            {{-- SEARCH + FILTER --}}
            <div class="flex gap-3 mb-4">
                <input type="text" placeholder="Cari nama rapat..."
                    class="w-1/3 px-3 py-2 border rounded-lg shadow-sm focus:ring-blue-300">

                <input type="date"
                    class="px-3 py-2 border rounded-lg shadow-sm focus:ring-blue-300">
            </div>
             @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            {{-- TABLE --}}
            <div class="overflow-x-auto bg-white p-4 rounded-xl shadow border border-gray-300">
                <table class="w-full text-sm text-left">

                    <thead class="text-xs uppercase bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center text-white">No</th>
                            <th class="px-4 py-3 w-20 text-white">Kode</th>
                            <th class="px-4 py-3 text-white">Evaluasi Kawasan Transmigrasi (TEP)</th>
                            <th class="px-4 py-3 text-white text-center w-32">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($kategoriList as $kategori)
                            
                            <!-- ========== KATEGORI ========== -->
                            <tr class="bg-gray-100 border-b">
                                <td class="p-3 text-center font-bold"></td>
                                <td class="p-3 font-bold">{{ $kategori->kode_kategori }}</td>
                                <td class="p-3 font-bold">{{ $kategori->nama_kategori }}</td>
                                <td></td>
                            </tr>

                            <!-- ========== ITEM ========== -->
                            @foreach ($kategori->items as $i => $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 text-center">{{ $i+1 }}</td>
                                <td class="p-3">{{ $item->kode_item }}</td>
                                <td class="p-3">{{ $item->nama_item }}</td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">

                                        <!-- EDIT -->
                                        <button 
                                            data-modal-target="edit-modal-{{ $item->item_id }}"
                                            data-modal-toggle="edit-modal-{{ $item->item_id }}"
                                            class="px-3 py-1 text-xs text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                            Edit
                                        </button>

                                        <!-- DELETE -->
                                        <button 
                                            data-modal-target="delete-modal-{{ $item->item_id }}"
                                            data-modal-toggle="delete-modal-{{ $item->item_id }}"
                                            class="px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700">
                                            Delete
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
            @endforeach

                        <div id="modal-evaluasi" tabindex="-1" aria-hidden="true"
                            class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full"
                            x-data="{ step: 1 }" x-cloak>

                            <div class="absolute inset-0 bg-black opacity-50" data-modal-hide="modal-evaluasi"></div>

                            <div class="relative w-full max-w-2xl bg-white rounded-lg shadow">
                                
                                <!-- HEADER -->
                                <div class="flex justify-between items-center p-4 border-b">
                                    <h3 class="text-xl font-semibold">
                                        Tambah Evaluasi Kawasan
                                    </h3>
                                    <button data-modal-hide="modal-evaluasi" class="text-gray-400 hover:text-gray-700">
                                        ✕
                                    </button>
                                </div>

                                <!-- BODY -->
                                <div class="p-6 space-y-4">

                                    <!-- STEP INDICATOR -->
                                    <div class="flex gap-2 justify-center mb-6">
                                        <div :class="step == 1 ? 'bg-blue-600 text-white' : 'bg-gray-300'"
                                            class="px-4 py-1 rounded-full text-sm font-medium">
                                            1. Kategori
                                        </div>

                                        <div :class="step == 2 ? 'bg-blue-600 text-white' : 'bg-gray-300'"
                                            class="px-4 py-1 rounded-full text-sm font-medium">
                                            2. Indikator
                                        </div>
                                    </div>


                                    <!-- STEP 1 : KATEGORI -->
                                    <div x-show="step === 1" x-transition>
                                        <form id="step1-form" action="{{ route('store-step1') }}" method="POST">
                                        @csrf
                                            <div class="mb-3">
                                                <label class="block mb-1 font-medium">Kode Kategori</label>
                                                <input type="text" name="kode_kategori" placeholder="A"
                                                    class="w-full border rounded-lg px-3 py-2" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="block mb-1 font-medium">Nama Kategori</label>
                                                <input type="text" name="nama_kategori" placeholder="Gambaran Umum Kawasan"
                                                    class="w-full border rounded-lg px-3 py-2" required>
                                            </div>

                                            <div class="flex justify-end">
                                                <button type="button" id="btn-step1" @click="step = 2"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                    Lanjut →
                                                </button>

                                            </div>
                                        </form>
                                    </div>


                                    <!-- STEP 2 : INDIKATOR -->
                                    <div x-show="step === 2" x-transition>
                                        <form id="form-step2" action="{{ route('store-step2') }}" method="POST">
                                            @csrf

                                            <input type="hidden" id="kategori_id" name="kategori_id" value="{{ session('kategori_id') }}">

                                            <div id="indikator-wrapper">
                                                <div class="flex gap-4 mb-3 indikator-row">
                                                    <input type="text" name="kode_item[]" class="border px-3 py-2 rounded w-24" placeholder="A1">
                                                    <input type="text" name="nama_item[]" class="border px-3 py-2 rounded w-full" placeholder="Nama indikator">
                                                    <button type="button" class="remove-btn text-red-500">Hapus</button>
                                                </div>
                                            </div>



                                            <button type="button" id="addRow" class="px-3 py-2 bg-blue-600 text-white rounded">
                                                + Tambah Indikator
                                            </button>

                                            <button type="button" id="btn-step2" class="px-4 py-2 bg-green-600 text-white rounded">
                                                Simpan Evaluasi
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('btn-step1').addEventListener('click', function (e) {
            e.preventDefault();

            let form = document.getElementById('step1-form');
            let formData = new FormData(form);

            fetch("{{ route('store-step1') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {

                    // Set kategori_id
                    document.getElementById('kategori_id').value = data.kategori_id;

                    // Ambil next kode item
                    fetch(`/user/evaluasiKategori/next-kode/${data.kategori_id}`)
                        .then(res => res.json())
                        .then(result => {
                            let inputKode = document.querySelector('input[name="kode_item[]"]');
                            inputKode.placeholder = result.next;
                            inputKode.value = result.next;
                        });

                    // Loncat ke step 2
                    document.querySelector('#modal-evaluasi').__x.$data.step = 2;
                }
            });
        });


        let rowIndex = 1; // mulai dari 1

        document.addEventListener('click', function (e) {
            if (e.target.id === 'addRow') {

                let kategoriId = document.getElementById('kategori_id').value;

                fetch(`/evaluasi/next-kode/${kategoriId}`)
                    .then(res => res.json())
                    .then(data => {

                        const wrapper = document.getElementById('indikator-wrapper');

                        const row = document.createElement('div');
                        row.classList.add('flex', 'gap-4', 'mb-3', 'indikator-row');

                        row.innerHTML = `
                            <input type="text" name="kode_item[]" class="border px-3 py-2 rounded w-24" value="${data.next}">
                            <input type="text" name="nama_item[]" class="border px-3 py-2 rounded w-full" placeholder="Nama indikator">
                            <button type="button" class="remove-btn text-red-500">Hapus</button>
                        `;

                        wrapper.appendChild(row);
                    });
            }
        });


        // Hapus row
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.parentElement.remove();
            }
        });

        document.getElementById('btn-step2').addEventListener('click', function (e) {
            e.preventDefault();

            let formData = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Ambil input indikator
            let kodeInputs = document.querySelectorAll('input[name="kode_item[]"]');
            let namaInputs = document.querySelectorAll('input[name="nama_item[]"]');

            let kodeList = [];

            // Loop indikator
            kodeInputs.forEach((input, i) => {
                let kode = input.value.trim();
                let nama = namaInputs[i].value.trim();

                if (kode !== "" && nama !== "") {
                    formData.append('kode_item[]', kode);
                    formData.append('nama_item[]', nama);
                    kodeList.push(kode);
                }
            });

            // Validasi duplikat
            if (new Set(kodeList).size !== kodeList.length) {
                alert("Kode indikator tidak boleh duplikat.");
                return;
            }

            // kategori
            const kategoriId = document.getElementById('kategori_id').value;
            formData.append('kategori_id', kategoriId);

            // Kirim ke backend
            fetch("{{ route('store-step2') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrfToken },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                    return;
                }

                alert("Berhasil disimpan!");
                location.reload();
            });
        });



    </script>

    
</body>

</html>