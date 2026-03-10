<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kawasan Transmigrasi</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo3.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('Logo3.png') }}">
    
    @vite('resources/css/app.css')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 min-h-screen">
    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="p-6 sm:ml-64 pt-24">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-700">Data Transmigrasi</h2>
            <button onclick="document.getElementById('modal-transmigrasi').classList.remove('hidden')"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-800 transition shadow">
                + Input Data Kawasan
            </button>
        </div>

        <div class="mb-4 flex items-center gap-3">
            <label class="font-semibold text-gray-700">Filter Provinsi:</label>
            <select id="filter-provinsi" class="px-3 py-2 border rounded-lg bg-white shadow-sm focus:ring-blue-500">
                <option value="">Semua Provinsi</option>
                @foreach ($provinsi as $prov)
                <option value="{{ $prov->id }}">{{ $prov->nama_provinsi }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-lg p-4">
            <table class="min-w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-700 text-white text-left">
                        <th class="p-3 border rounded-tl-lg">No</th>
                        <th class="p-3 border">Provinsi</th>
                        <th class="p-3 border">Kabupaten</th>
                        <th class="p-3 border">Nama Kawasan</th>
                        <th class="p-3 border">Kode Kawasan</th>
                        <th class="p-3 border text-center rounded-tr-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-body-kawasan" class="divide-y">
                    @foreach ($kawasan as $index => $item)
                    <tr class="hover:bg-gray-50 transition item-row" data-provinsi="{{ $item->provinsi_id }}">
                        <td class="p-3 border row-number text-center font-medium">{{ $index + 1 }}</td>
                        <td class="p-3 border">{{ $item->provinsi->nama_provinsi ?? '-' }}</td>
                        <td class="p-3 border">{{ $item->kabupaten->nama_kabupaten ?? '-' }}</td>
                        <td class="p-3 border font-semibold text-gray-800">{{ $item->nama_kawasan }}</td>
                        <td class="p-3 border font-mono text-gray-600">{{ $item->kode_kawasan }}</td>
                        <td class="p-3 border">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="loadDetail({{ $item->id }})" class="detail-btn px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs font-medium">Detail</button>
                                
                                <button data-modal-target="edit-modal-{{ $item->id }}" 
                                        data-modal-toggle="edit-modal-{{ $item->id }}" 
                                        class="px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs font-medium">
                                    Edit
                                </button>

                                <button data-modal-target="delete-modal-{{ $item->id }}" 
                                        data-modal-toggle="delete-modal-{{ $item->id }}" 
                                        class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-800 text-xs font-medium">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div id="pagination-controls" class="flex justify-end items-center mt-4 gap-2"></div>
        </div>

        @foreach ($kawasan as $item)
        <div id="edit-modal-{{ $item->id }}" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full">
            <div class="absolute inset-0 bg-black/60 transition-opacity" data-modal-hide="edit-modal-{{ $item->id }}"></div>
            
            <div class="relative w-full max-w-4xl mx-4 bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-5 border-b bg-gray-50 sticky top-0 z-10">
                    <h3 class="text-xl font-bold text-gray-800">Edit Data: {{ $item->nama_kawasan }}</h3>
                    <button data-modal-hide="edit-modal-{{ $item->id }}" class="text-gray-400 hover:text-gray-700 text-2xl font-bold">×</button>
                </div>

                <div class="p-6 space-y-6" x-data="{ step: 1 }" x-on:next-step-edit-{{ $item->id }}.window="step = 2">
                    <div class="flex justify-center gap-4 mb-6">
                        <div :class="step == 1 ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-500'" class="px-5 py-2 rounded-full text-sm font-bold transition-all duration-300">1. Data Kawasan</div>
                        <div :class="step == 2 ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-500'" class="px-5 py-2 rounded-full text-sm font-bold transition-all duration-300">2. Data TEP</div>
                    </div>

                    <div x-show="step === 1" x-transition.opacity.duration.300ms>
                        <form id="form-edit-step1-{{ $item->id }}" method="POST" action="{{ route('updateKawasan', $item->id) }}">
                            @csrf @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-bold text-blue-800 uppercase border-b pb-1 mb-3">Identitas Wilayah & Lokasi</h4>
                                </div>
                                
                                <div><label class="block text-sm font-medium mb-1">Nama Kawasan</label><input type="text" name="nama_kawasan" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->nama_kawasan }}"></div>
                                <div><label class="block text-sm font-medium mb-1">Kode Kawasan</label><input type="text" name="kode_kawasan" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->kode_kawasan }}"></div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1">Provinsi</label>
                                    <select name="provinsi_id" id="provinsi_id_{{ $item->id }}" 
                                            class="w-full border-gray-300 rounded-lg px-3 py-2 provinsi-edit-select" 
                                            data-id="{{ $item->id }}">
                                        
                                        <option value="">-- Pilih Provinsi --</option>
                                        
                                        @foreach ($daftarProvinsiMaster as $p)
                                            @php
                                                $namaMaster = strtolower($p->name);
                                                $namaSelected = strtolower($item->provinsi->nama_provinsi ?? '');
                                                $isSelected = ($namaMaster == $namaSelected) ? 'selected' : '';
                                            @endphp
                                            
                                            <option value="{{ $p->id }}" {{ $isSelected }}>
                                                {{ ucwords(strtolower($p->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Kabupaten</label>
                                    @php
                                        // Cari ID Master Kabupaten berdasarkan Nama Kabupaten yang tersimpan
                                        // Ini agar saat edit dibuka, kabupatennya langsung terpilih
                                        $kabupatenMasterId = '';
                                        if($item->kabupaten) {
                                            $kabMaster = \App\Models\Regency::where('name', $item->kabupaten->nama_kabupaten)->first();
                                            $kabupatenMasterId = $kabMaster ? $kabMaster->id : '';
                                        }
                                    @endphp

                                    <select name="kabupaten_id" id="kabupaten_id_{{ $item->id }}" 
                                            class="w-full border-gray-300 rounded-lg px-3 py-2 kabupaten-edit-select" 
                                            data-selected="{{ $kabupatenMasterId }}">
                                        <option value="">-- Pilih Kabupaten --</option>
                                    </select>
                                </div>

                                <div><label class="block text-sm font-medium mb-1">Kode Lokasi</label><input type="text" name="kode_lokasi" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->kode_lokasi }}"></div>
                                <div><label class="block text-sm font-medium mb-1">Nama Lokasi</label><input type="text" name="nama_lokasi" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->nama_lokasi }}"></div>

                                <div><label class="block text-sm font-medium mb-1">Latitude</label><input type="text" name="latitude" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->latitude }}"></div>
                                <div><label class="block text-sm font-medium mb-1">Longitude</label><input type="text" name="longitude" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->longitude }}"></div>
                                
                                <div class="md:col-span-2 mt-2">
                                    <h4 class="text-sm font-bold text-green-800 uppercase border-b pb-1 mb-3">Statistik & Ekonomi</h4>
                                </div>

                                <div><label class="block text-sm font-medium mb-1">Jumlah Desa</label><input type="number" name="jumlah_desa" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->jumlah_desa }}"></div>
                                <div><label class="block text-sm font-medium mb-1">Jumlah Penduduk</label><input type="number" name="jumlah_penduduk" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->jumlah_penduduk }}"></div>
                                <div><label class="block text-sm font-medium mb-1">Intrans</label><input type="text" name="intrans" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->intrans }}"></div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">Produk Unggulan</label>
                                    <textarea name="produk_unggulan" rows="2" class="w-full border-gray-300 rounded-lg px-3 py-2">{{ $item->produk_unggulan }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">Potensi Kawasan</label>
                                    <textarea name="potensi" rows="2" class="w-full border-gray-300 rounded-lg px-3 py-2">{{ $item->potensi ?? '' }}</textarea>
                                </div>
                                
                                <div><label class="block text-sm font-medium mb-1">Pendapatan/Kapita</label><input type="number" name="pendapatan_perkapita" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->pendapatan_perkapita }}"></div>
                                <div><label class="block text-sm font-medium mb-1">Investasi</label><input type="number" name="investasi" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->investasi }}"></div>
                                <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Kegiatan Kolaborasi</label><input type="text" name="keg_kolaborasi" class="w-full border-gray-300 rounded-lg px-3 py-2" value="{{ $item->keg_kolaborasi }}"></div>

                                <div class="md:col-span-2 mt-2">
                                    <h4 class="text-sm font-bold text-orange-800 uppercase border-b pb-1 mb-3">Status Perkembangan Desa</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Mandiri</label><input type="number" name="desa_mandiri" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" value="{{ $item->desa_mandiri }}"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Maju</label><input type="number" name="desa_maju" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" value="{{ $item->desa_maju }}"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Berkembang</label><input type="number" name="desa_berkembang" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" value="{{ $item->desa_berkembang }}"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Tertinggal</label><input type="number" name="desa_tertinggal" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" value="{{ $item->desa_tertinggal }}"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Sgt Tertinggal</label><input type="number" name="desa_sangat_tertinggal" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" value="{{ $item->desa_sangat_tertinggal }}"></div>
                                    </div>
                                </div>

                                <div class="md:col-span-2 mt-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <h4 class="font-bold text-gray-700 mb-3 text-sm">Akun Pengelola (User Login)</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase mb-1">Email Login</label>
                                            <input type="email" name="email_pengelola" class="w-full text-sm border-gray-300 rounded-lg px-3 py-2" value="{{ $item->user->email ?? '' }}" placeholder="email@contoh.com">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end mt-8 pt-4 border-t">
                                <button type="button" onclick="submitStep1Edit({{ $item->id }})" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-lg transition">Simpan & Lanjut Data TEP →</button>
                            </div>
                        </form>
                    </div>

                    <div x-show="step === 2" x-transition.opacity.duration.300ms x-cloak>
                        <form method="POST" action="{{ route('updateDataTEP', $item->id) }}">
                            @csrf @method('PUT')
                            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach ($indikator as $i)
                                @php $nilai = $item->tep->where('indikator_id', $i->id)->first()->nilai ?? ''; @endphp
                                <div class="grid grid-cols-12 gap-4 items-center border border-gray-200 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="col-span-2 font-mono text-sm font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded text-center">{{ $i->kode_indikator }}</div>
                                    <div class="col-span-7 text-sm font-medium text-gray-700">{{ $i->nama_indikator }}</div>
                                    <div class="col-span-3">
                                        <input type="hidden" name="indikator_id[]" value="{{ $i->id }}">
                                        <input type="number" name="nilai[]" value="{{ $nilai }}" class="w-full border-gray-300 px-3 py-2 rounded" placeholder="0">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-6 pt-4 border-t">
                                <button type="button" @click="step = 1" class="px-5 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">← Kembali</button>
                                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 shadow-lg transition">Simpan Semua Perubahan ✓</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="delete-modal-{{ $item->id }}" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6 text-center transform scale-100 transition-all">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Data?</h3>
                <p class="mb-6 text-gray-500">Yakin ingin menghapus kawasan <br> <span class="font-bold text-red-600">"{{ $item->nama_kawasan }}"</span>?</p>
                <form method="POST" action="{{ route('deleteKawasan', $item->id) }}" class="flex justify-center gap-3">
                    @csrf @method('DELETE')
                    <button type="button" data-modal-hide="delete-modal-{{ $item->id }}" class="px-5 py-2.5 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 font-medium transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white bg-red-600 rounded-lg hover:bg-red-700 font-medium shadow-lg transition">Ya, Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
        
        <div id="modal-transmigrasi" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full">
            <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="document.getElementById('modal-transmigrasi').classList.add('hidden')"></div>

            <div class="relative w-full max-w-4xl mx-4 bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-5 border-b bg-gray-50 sticky top-0 z-10">
                    <h3 class="text-xl font-bold text-gray-800">+ Input Data Baru</h3>
                    <button onclick="document.getElementById('modal-transmigrasi').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-2xl font-bold">×</button>
                </div>

                <div class="p-6 space-y-6" x-data="{ step: 1 }" x-on:next-step.window="step = $event.detail" x-cloak>
                    <div class="flex justify-center gap-4 mb-6">
                        <div :class="step == 1 ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-500'" class="px-5 py-2 rounded-full text-sm font-bold transition-all duration-300">1. Data Kawasan</div>
                        <div :class="step == 2 ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-500'" class="px-5 py-2 rounded-full text-sm font-bold transition-all duration-300">2. Data TEP</div>
                    </div>

                    <div x-show="step === 1" x-transition>
                        <form id="form-step1" method="POST" action="{{ route('storeDataKawasan') }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div><label class="block mb-1 font-medium text-sm">Nama Lokasi</label><input type="text" name="nama_lokasi" class="w-full border-gray-300 rounded-lg px-3 py-2" required></div>
                                
                                <div><label class="block mb-1 font-medium text-sm">Latitude</label><input type="text" name="latitude" class="w-full border-gray-300 rounded-lg px-3 py-2" placeholder="-2.5489"></div>
                                <div><label class="block mb-1 font-medium text-sm">Longitude</label><input type="text" name="longitude" class="w-full border-gray-300 rounded-lg px-3 py-2" placeholder="118.0149"></div>

                                <div class="md:col-span-2"><h4 class="text-sm font-bold text-blue-800 uppercase border-b pb-1 mb-3">Lokasi & Identitas</h4></div>
                                
                                <div>
                                    <label class="block mb-1 font-medium text-sm">Provinsi</label>
                                    <select id="provinsi_id" name="provinsi_id" class="w-full border-gray-300 rounded-lg px-3 py-2" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach ($daftarProvinsiMaster as $p)
                                            <option value="{{ $p->id }}">{{ ucwords(strtolower($p->name)) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block mb-1 font-medium text-sm">Kabupaten</label>
                                    <select id="kabupaten_id" name="kabupaten_id" class="w-full border-gray-300 rounded-lg px-3 py-2" required>
                                        <option value="">-- Pilih Provinsi Terlebih Dahulu --</option>
                                    </select>
                                </div>

                                <div><label class="block mb-1 font-medium text-sm">Nama Kawasan</label><input type="text" name="nama_kawasan" class="w-full border-gray-300 rounded-lg px-3 py-2" required></div>
                                <div><label class="block mb-1 font-medium text-sm">Kode Kawasan</label><input type="text" name="kode_kawasan" class="w-full border-gray-300 rounded-lg px-3 py-2" required></div>
                                <div><label class="block mb-1 font-medium text-sm">Kode Lokasi</label><input type="text" name="kode_lokasi" class="w-full border-gray-300 rounded-lg px-3 py-2" required></div>
                                
                                <div class="md:col-span-2 mt-2"><h4 class="text-sm font-bold text-green-800 uppercase border-b pb-1 mb-3">Data Statistik</h4></div>
                                
                                <div><label class="block mb-1 font-medium text-sm">Jumlah Desa</label><input type="number" name="jumlah_desa" class="w-full border-gray-300 rounded-lg px-3 py-2" required></div>
                                <div><label class="block mb-1 font-medium text-sm">Jumlah Penduduk</label><input type="number" name="jumlah_penduduk" class="w-full border-gray-300 rounded-lg px-3 py-2" required></div>
                                <div><label class="block mb-1 font-medium text-sm">Intrans</label><input type="text" name="intrans" class="w-full border-gray-300 rounded-lg px-3 py-2"></div>
                                <div class="md:col-span-2"><label class="block mb-1 font-medium text-sm">Produk Unggulan</label><textarea name="produk_unggulan" rows="2" class="w-full border-gray-300 rounded-lg px-3 py-2"></textarea></div>
                                <div class="md:col-span-2"><label class="block mb-1 font-medium text-sm">Potensi Kawasan</label><textarea name="potensi" rows="2" class="w-full border-gray-300 rounded-lg px-3 py-2"></textarea></div>
                                <div><label class="block mb-1 font-medium text-sm">Pendapatan/Kapita</label><input type="number" name="pendapatan_perkapita" class="w-full border-gray-300 rounded-lg px-3 py-2"></div>
                                <div><label class="block mb-1 font-medium text-sm">Investasi</label><input type="number" name="investasi" class="w-full border-gray-300 rounded-lg px-3 py-2"></div>
                                <div class="md:col-span-2"><label class="block mb-1 font-medium text-sm">Kegiatan Kolaborasi</label><input type="text" name="keg_kolaborasi" class="w-full border-gray-300 rounded-lg px-3 py-2"></div>

                                <div class="md:col-span-2 mt-2">
                                    <h4 class="text-sm font-bold text-orange-800 uppercase border-b pb-1 mb-3">Status Perkembangan Desa</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Mandiri</label><input type="number" name="desa_mandiri" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" placeholder="0"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Maju</label><input type="number" name="desa_maju" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" placeholder="0"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Berkembang</label><input type="number" name="desa_berkembang" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" placeholder="0"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Tertinggal</label><input type="number" name="desa_tertinggal" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" placeholder="0"></div>
                                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Sgt Tertinggal</label><input type="number" name="desa_sangat_tertinggal" class="w-full bg-orange-50 border-orange-200 rounded px-2 py-1 text-sm font-bold text-center" placeholder="0"></div>
                                    </div>
                                </div>

                                <div class="md:col-span-2 mt-2">
                                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                        <h4 class="font-bold text-gray-800 text-sm mb-3">Buat Akun Login Pengelola (Opsional)</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div><label class="block text-xs font-semibold uppercase mb-1">Email Login</label><input type="email" name="email_pengelola" class="w-full text-sm border-gray-300 rounded-lg px-3 py-2"></div>
                                            <div><label class="block text-xs font-semibold uppercase mb-1">Password</label><input type="password" name="password_pengelola" class="w-full text-sm border-gray-300 rounded-lg px-3 py-2" placeholder="Default: password123"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-8 border-t pt-4">
                                <button id="btn-step1" type="button" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-lg transition">Lanjut Isi Data TEP →</button>
                            </div>
                        </form>
                    </div>

                    <div x-show="step === 2" x-transition x-cloak>
                        <form id="form-step2" method="POST" action="{{ route('storeDataTEP') }}">
                            @csrf
                            <input type="hidden" id="kawasan_id_step2" name="kawasan_id" value="">
                            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($indikator as $i)
                                <div class="grid grid-cols-12 gap-4 items-center border border-gray-200 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="col-span-2 font-mono text-sm font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded text-center">{{ $i->kode_indikator }}</div>
                                    <div class="col-span-7 text-sm font-medium text-gray-700">{{ $i->nama_indikator }}</div>
                                    <div class="col-span-3">
                                        <input type="hidden" name="indikator_id[]" value="{{ $i->id }}">
                                        <input type="number" name="nilai[]" placeholder="0" class="w-full border-gray-300 px-3 py-2 rounded" required>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-6 pt-4 border-t">
                                <button type="button" @click="step = 1" class="px-5 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">← Kembali</button>
                                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg transition">Simpan Semua Data ✓</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-detail" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-[9999] p-4 transition-opacity duration-300">
            <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden animate-[fadeIn_0.2s] max-h-[90vh] flex flex-col">
                <div class="px-8 py-5 bg-[#0b3a68] flex justify-between items-center shrink-0">
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-wide">Detail Lengkap Kawasan</h2>
                        <p class="text-xs text-blue-200 mt-1">Data Kawasan Transmigrasi Terpadu</p>
                    </div>
                    <button onclick="closeModal()" class="text-white/70 hover:text-white hover:bg-white/10 rounded-full p-2 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-8 overflow-y-auto custom-scrollbar bg-gray-50/50 space-y-8">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-800 uppercase border-b pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            Identitas & Lokasi
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Nama Kawasan</p><p id="detail-nama_kawasan" class="text-base font-bold text-gray-900 mt-1">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Kode Kawasan</p><p id="detail-kode_kawasan" class="text-base font-mono font-medium text-blue-700 mt-1 bg-blue-50 inline-block px-2 rounded">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Provinsi</p><p id="detail-provinsi" class="text-base font-medium text-gray-900 mt-1">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Kabupaten</p><p id="detail-kabupaten" class="text-base font-medium text-gray-900 mt-1">-</p></div>
                            
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Latitude</p><p id="detail-latitude" class="text-base font-mono text-gray-700 mt-1">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Longitude</p><p id="detail-longitude" class="text-base font-mono text-gray-700 mt-1">-</p></div>
                            <div class="col-span-2 flex items-end"><a id="link-maps" href="#" target="_blank" class="hidden text-sm text-blue-600 hover:underline flex items-center gap-1">📍 Lihat di Google Maps</a></div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-800 uppercase border-b pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Statistik & Ekonomi
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div><p class="text-xs text-gray-500 mb-1">Jumlah Desa</p><p class="text-lg font-bold text-gray-800"><span id="detail-jumlah_desa">-</span> Desa</p></div>
                            <div><p class="text-xs text-gray-500 mb-1">Penduduk</p><p class="text-lg font-bold text-gray-800"><span id="detail-jumlah_penduduk">-</span> Jiwa</p></div>
                            <div><p class="text-xs text-gray-500 mb-1">Intrans</p><p class="text-lg font-bold text-gray-800"><span id="detail-intrans">-</span> KK</p></div>
                            <div><p class="text-xs text-gray-500 mb-1">Investasi</p><p class="text-lg font-bold text-gray-800">Rp <span id="detail-investasi">-</span></p></div>
                            
                            <div class="col-span-2 bg-yellow-50 p-3 rounded border border-yellow-100"><p class="text-xs font-bold text-yellow-800 mb-1">Produk Unggulan</p><p id="detail-produk_unggulan" class="text-sm text-gray-800">-</p></div>
                            <div class="col-span-2 bg-blue-50 p-3 rounded border border-blue-100"><p class="text-xs font-bold text-blue-800 mb-1">Potensi</p><p id="detail-potensi" class="text-sm text-gray-800">-</p></div>
                        </div>
                    </div>

                    <div class="bg-orange-50 p-6 rounded-xl border border-orange-100 shadow-sm">
                        <h3 class="text-sm font-bold text-orange-900 uppercase border-b border-orange-200 pb-3 mb-4">Status Perkembangan Desa</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-center">
                            <div class="bg-white p-3 rounded shadow-sm"><p class="text-[10px] uppercase font-bold text-gray-500">Mandiri</p><p id="detail-desa_mandiri" class="text-2xl font-bold text-blue-600">-</p></div>
                            <div class="bg-white p-3 rounded shadow-sm"><p class="text-[10px] uppercase font-bold text-gray-500">Maju</p><p id="detail-desa_maju" class="text-2xl font-bold text-green-600">-</p></div>
                            <div class="bg-white p-3 rounded shadow-sm"><p class="text-[10px] uppercase font-bold text-gray-500">Berkembang</p><p id="detail-desa_berkembang" class="text-2xl font-bold text-yellow-600">-</p></div>
                            <div class="bg-white p-3 rounded shadow-sm"><p class="text-[10px] uppercase font-bold text-gray-500">Tertinggal</p><p id="detail-desa_tertinggal" class="text-2xl font-bold text-orange-600">-</p></div>
                            <div class="bg-white p-3 rounded shadow-sm"><p class="text-[10px] uppercase font-bold text-gray-500">Sgt Tertinggal</p><p id="detail-desa_sangat_tertinggal" class="text-2xl font-bold text-red-600">-</p></div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-4 bg-gray-100 border-t flex justify-end shrink-0">
                    <button onclick="closeModal()" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-200">Tutup</button>
                </div>
            </div>
        </div>

    </div>

<script>
    // --- 1. MODAL & SCROLL LOGIC ---
    window.openModal = function() {
        const modal = document.getElementById('modal-detail');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }
    window.closeModal = function() {
        const modal = document.getElementById('modal-detail');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // --- 2. LOGIKA LOAD DATA KABUPATEN (AJAX) ---
    function fetchKabupaten(provId, targetElement, selectedKabId = null) {
        if (!provId) { 
            targetElement.innerHTML = '<option value="">-- Pilih Kabupaten --</option>'; 
            return; 
        }
        
        targetElement.innerHTML = '<option value="">Memuat data...</option>';
        
        fetch(`/admin/get-kabupaten/${provId}`)
            .then(res => res.json())
            .then(data => {
                targetElement.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
                
                if(data.length === 0) {
                    targetElement.innerHTML = '<option value="">Data Kosong</option>';
                }

                data.forEach(item => {
                    const isSelected = item.id == selectedKabId ? 'selected' : '';
                    // Data dari Regency Master biasanya 'name'
                    const namaKab = item.name || item.nama_kabupaten || 'Tanpa Nama';
                    
                    // Capitalize agar rapi
                    targetElement.innerHTML += `<option value="${item.id}" class="capitalize" ${isSelected}>${namaKab.toLowerCase()}</option>`;
                });
            })
            .catch(err => {
                console.error(err);
                targetElement.innerHTML = '<option value="">Gagal memuat</option>';
            });
    }

    // --- 3. DOM LOADED (UTAMA) ---
    document.addEventListener("DOMContentLoaded", function () {
        
        // A. Handle Dropdown di Modal Tambah (Create)
        const provInput = document.getElementById('provinsi_id');
        const kabInput = document.getElementById('kabupaten_id');
        
        if (provInput && kabInput) {
            provInput.addEventListener('change', function() { 
                fetchKabupaten(this.value, kabInput); 
            });
        }

        // B. Event Listener Global untuk Tombol Edit & Detail
        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('[data-modal-target^="edit-modal-"]');
            const detailBtn = e.target.closest('.detail-btn');

            // --- Logic Tombol Edit ---
            // --- Logic Tombol Edit (PERBAIKAN) ---
            if (editBtn) {
                const id = editBtn.getAttribute('data-modal-target').replace('edit-modal-', '');
                const provSelect = document.getElementById(`provinsi_id_${id}`);
                const kabSelect = document.getElementById(`kabupaten_id_${id}`);
                
                if(provSelect && kabSelect) {
                    // Ambil ID Kabupaten Master yang sudah kita hitung di PHP tadi
                    const selectedKabMasterId = kabSelect.getAttribute('data-selected');
                    
                    // 1. Load data kabupaten berdasarkan Value Provinsi (Sekarang Value-nya adalah ID Master)
                    // Kita panggil fungsi fetchKabupaten
                    fetchKabupaten(provSelect.value, kabSelect, selectedKabMasterId);

                    // 2. Pasang listener change jika user mengubah provinsi di form edit
                    // Cek dulu biar event listener gak numpuk (double)
                    if (!provSelect.dataset.hasListener) {
                        provSelect.addEventListener('change', function() {
                            // Saat berubah, load kabupaten baru, reset selected
                            fetchKabupaten(this.value, kabSelect, null);
                        });
                        provSelect.dataset.hasListener = "true";
                    }
                }
            }

            // --- Logic Tombol Detail ---
            if (detailBtn) {
                const id = detailBtn.getAttribute('onclick')?.match(/\d+/)?.[0] || detailBtn.getAttribute('data-id');
                if(id) loadDetail(id);
            }
        });

        // C. Pagination Logic (TETAP SAMA)
        const rowsPerPage = 10;
        let currentPage = 1;
        const filterSelect = document.getElementById('filter-provinsi');
        const allRows = Array.from(document.querySelectorAll('#tabel-body-kawasan > tr'));

        function updateTable() {
            const filterValue = filterSelect?.value || '';
            let visibleRows = allRows.filter(row => filterValue === '' || row.getAttribute('data-provinsi') === filterValue);
            
            const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
            if (currentPage > totalPages) currentPage = (totalPages > 0 ? totalPages : 1);
            
            allRows.forEach(row => row.style.display = 'none');
            
            visibleRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach((row, index) => {
                row.style.display = '';
                const rowNum = row.querySelector('.row-number');
                if(rowNum) rowNum.innerText = (currentPage - 1) * rowsPerPage + index + 1;
            });
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            const container = document.getElementById('pagination-controls');
            if(!container) return; 
            container.innerHTML = '';
            
            if (totalPages > 1) {
                const prev = document.createElement('button');
                prev.innerText = '<';
                prev.className = "px-3 py-1 border rounded hover:bg-gray-100 text-xs mx-1";
                prev.disabled = currentPage === 1;
                prev.onclick = () => { if(currentPage > 1) { currentPage--; updateTable(); }};
                container.appendChild(prev);
            }

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                btn.className = (i === currentPage) ? 
                    "px-3 py-1 bg-blue-600 text-white rounded shadow text-xs mx-1" : 
                    "px-3 py-1 bg-white border rounded hover:bg-gray-100 text-xs mx-1";
                btn.onclick = () => { currentPage = i; updateTable(); };
                container.appendChild(btn);
            }

            if (totalPages > 1) {
                const next = document.createElement('button');
                next.innerText = '>';
                next.className = "px-3 py-1 border rounded hover:bg-gray-100 text-xs mx-1";
                next.disabled = currentPage === totalPages;
                next.onclick = () => { if(currentPage < totalPages) { currentPage++; updateTable(); }};
                container.appendChild(next);
            }
        }

        if(filterSelect) filterSelect.onchange = () => { currentPage = 1; updateTable(); };
        updateTable();

        // D. Submit Create Step 1 (AJAX)
        const btnStep1 = document.getElementById('btn-step1');
        if(btnStep1){
            btnStep1.onclick = function() {
                let form = document.getElementById('form-step1');
                let btn = this;
                
                if(!form.checkValidity()) { form.reportValidity(); return; }

                let originalText = btn.innerText;
                btn.innerText = "Menyimpan...";
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');

                fetch("{{ route('storeDataKawasan') }}", {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content, "Accept": "application/json" },
                    body: new FormData(form)
                })
                .then(res => res.json())
                .then(data => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');

                    if(data.success) {
                        document.getElementById('kawasan_id_step2').value = data.kawasan_id;
                        window.dispatchEvent(new CustomEvent('next-step', { detail: 2 }));
                    } else {
                        let msg = data.message || "Gagal menyimpan data.";
                        if(data.errors) msg += "\n" + Object.values(data.errors).flat().join("\n");
                        alert(msg);
                    }
                })
                .catch(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    alert("Terjadi kesalahan sistem.");
                });
            };
        }

        // E. Submit Edit Step 1 (Global)
        window.submitStep1Edit = function(id) {
            const form = document.getElementById('form-edit-step1-' + id);
            const formData = new FormData(form);
            formData.append('_method', 'PUT');
            
            fetch(form.action, {
                method: 'POST',
                headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content, "Accept": "application/json" },
                body: formData
            }).then(res => res.json()).then(data => {
                if(data.success) window.dispatchEvent(new CustomEvent('next-step-edit-' + id));
                else alert(data.errors?.email_pengelola?.[0] || "Gagal update data.");
            });
        };
    });

    // --- 4. LOGIKA LOAD DETAIL (TETAP SAMA) ---
    window.loadDetail = function(id) {
        document.getElementById('detail-nama_kawasan').innerText = 'Loading...';
        
        fetch(`/admin/dataTransmigrasi/detail/${id}`)
            .then(res => res.json())
            .then(result => {
                const k = result.kawasan || result;
                
                const setText = (elemId, val) => {
                    const el = document.getElementById(elemId);
                    if(el) el.innerText = (val !== null && val !== undefined && val !== '') ? val : '-';
                };

                setText('detail-nama_kawasan', k.nama_kawasan);
                setText('detail-kode_kawasan', k.kode_kawasan);
                setText('detail-provinsi', k.provinsi?.nama_provinsi);
                setText('detail-kabupaten', k.kabupaten?.nama_kabupaten);
                setText('detail-latitude', k.latitude);
                setText('detail-longitude', k.longitude);
                
                const linkMaps = document.getElementById('link-maps');
                if(linkMaps) {
                    if (k.latitude && k.longitude) {
                        linkMaps.href = `https://www.google.com/maps?q=${k.latitude},${k.longitude}`;
                        linkMaps.classList.remove('hidden');
                    } else {
                        linkMaps.classList.add('hidden');
                    }
                }

                setText('detail-jumlah_desa', k.jumlah_desa);
                setText('detail-jumlah_penduduk', k.jumlah_penduduk);
                setText('detail-intrans', k.intrans);
                setText('detail-investasi', k.investasi);
                setText('detail-produk_unggulan', k.produk_unggulan);
                setText('detail-potensi', k.potensi);

                setText('detail-desa_mandiri', k.desa_mandiri ?? 0);
                setText('detail-desa_maju', k.desa_maju ?? 0);
                setText('detail-desa_berkembang', k.desa_berkembang ?? 0);
                setText('detail-desa_tertinggal', k.desa_tertinggal ?? 0);
                setText('detail-desa_sangat_tertinggal', k.desa_sangat_tertinggal ?? 0);

                openModal();
            })
            .catch(err => {
                console.error(err);
                alert("Gagal memuat detail data. Cek console untuk error.");
            });
    }
</script>
</body>
</html>