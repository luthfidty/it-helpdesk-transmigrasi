<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Indeks Infrastruktur</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo3.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('Logo3.png') }}">
    
    @vite('resources/css/app.css')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen">
@include('layouts.header')
@include('layouts.sidebar')

<div class="p-6 sm:ml-64 pt-24">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-700">
            Data Indeks Infrastruktur
        </h1>

        <!-- <button data-modal-target="modal-indeks-infra"
                data-modal-toggle="modal-indeks-infra"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-800">
            Input Indeks Infrastruktur
        </button> -->
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded-xl shadow-lg">
        <table class="min-w-full text-sm border-collapse">

            <thead class="bg-gray-700 text-white">
                <tr>
                    <th class="p-3 border">No</th>
                    <th class="p-3 border">Kawasan</th>
                    <th class="p-3 border text-center">Indeks Infrastruktur</th>
                    <th class="p-3 border text-center">Transportasi</th>
                    <th class="p-3 border text-center">Telekomunikasi</th>
                    <th class="p-3 border text-center">Air Bersih</th>
                    <th class="p-3 border text-center">Kesehatan</th>
                    <th class="p-3 border text-center">Listrik</th>
                    <th class="p-3 border text-center">Sarpras Komoditas</th>
                    <!-- <th class="p-3 border text-center">Aksi</th> -->
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($indeksInfra as $index => $item)
                    <tr class="hover:bg-gray-100">
                        <td class="p-3 border text-center">
                            {{ $index + 1 }}
                        </td>

                        <td class="p-3 border">
                            {{ $item->kawasan->nama_kawasan }}
                        </td>

                        <td class="p-3 border text-center">
                            {{ number_format($item->indeks_infrastruktur, 2) }}
                        </td>
                        <td class="p-3 border text-center">
                            {{ number_format($item->transportasi, 2) }}
                        </td>
                        <td class="p-3 border text-center">
                            {{ number_format($item->telekomunikasi, 2) }}
                        </td>
                        <td class="p-3 border text-center">
                            {{ number_format($item->air_bersih, 2) }}
                        </td>
                        <td class="p-3 border text-center">
                            {{ number_format($item->kesehatan, 2) }}
                        </td>
                        <td class="p-3 border text-center">
                            {{ number_format($item->listrik, 2) }}
                        </td>
                        <td class="p-3 border text-center">
                            {{ number_format($item->sarpras_komoditas, 2) }}
                        </td>

                        <!-- <td class="p-3 border">
                            <div class="flex justify-center gap-2">
                                
                                <button class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg">
                                    Edit
                                </button>

                                <button class="px-3 py-1.5 bg-red-500 text-white rounded-lg">
                                    Hapus
                                </button>
                            </div>
                        </td> -->
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            Data indeks infrastruktur belum tersedia
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</div>
<!-- Modal Tambah Indeks Infrastruktur -->
 <div id="modal-indeks-infra" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">

    <div class="relative w-full max-w-4xl mx-auto p-4">

        <div class="bg-white rounded-2xl shadow-lg">

            <!-- HEADER -->
            <div class="flex items-center justify-between px-6 py-3 border-b">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        Input Indeks Infrastruktur
                    </h3>
                    <p class="text-sm text-gray-500">
                        Data diisi berdasarkan hasil penilaian
                    </p>
                </div>
                <button type="button"
                    data-modal-hide="modal-indeks-infra"
                    class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <!-- BODY -->
            <form action="{{ route('storeIndeksInfra') }}" method="POST"
                class="px-6 pt-2 pb-6 space-y-2 max-h-[75vh] overflow-y-auto">
                @csrf

                <!-- Kawasan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Kawasan Prioritas
                    </label>
                    <select name="kawasan_prioritas_id" required
                        class="w-full rounded-xl border-gray-300 px-4 py-2 focus:ring-2 focus:ring-slate-800">
                        <option value="">-- Pilih Kawasan Prioritas --</option>
                        @foreach ($kawasan as $k)
                            <option value="{{ $k->id }}">
                                {{ $k->provinsi->nama_provinsi }} — {{ $k->nama_kawasan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Indeks -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Indeks Infrastruktur
                    </label>
                    <input type="number" step="0.01" min="0" max="100"
                        name="indeks_infrastruktur" required
                        placeholder="Contoh: 65.75"
                        class="w-full rounded-xl border-gray-300 px-4 py-2">
                </div>

                <!-- Indikator -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            $fields = [
                                'transportasi' => 'Transportasi',
                                'telekomunikasi' => 'Telekomunikasi',
                                'air_bersih' => 'Air Bersih',
                                'kesehatan' => 'Kesehatan',
                                'listrik' => 'Listrik',
                                'sarpras_komoditas' => 'Sarpras Komoditas',
                            ];
                        @endphp

                        @foreach ($fields as $name => $label)
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">
                                    {{ $label }}
                                </label>
                                <input type="number" step="0.01" min="0" max="5"
                                    name="{{ $name }}" required
                                    placeholder="0 – 5"
                                    class="w-full rounded-xl border-gray-300 px-4 py-2 focus:ring-2 focus:ring-slate-800">
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button"
                        data-modal-hide="modal-indeks-infra"
                        class="px-5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2 rounded-xl bg-slate-800 text-white hover:bg-slate-900">
                        Simpan Data
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>
