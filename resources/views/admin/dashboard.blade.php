<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Dashboard Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo3.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('Logo3.png') }}">
    @vite('resources/css/app.css') 
</head>
<body class="bg-gray-100 min-h-screen">
    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="p-6 sm:ml-64 mt-20">

        {{-- ======= STATISTIK CARD ======= --}}

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="p-5 bg-white rounded-xl shadow border border-gray-100">
                <p class="text-sm text-gray-500">Total Kawasan Transmigrasi</p>
                <h3 class="text-3xl font-bold text-green-600 mt-1">{{ $totalKawasan }}</h3>
            </div>

            <div class="p-5 bg-white rounded-xl shadow border border-gray-100">
                <p class="text-sm text-gray-500">Total Provinsi</p>
                <h3 class="text-3xl font-bold text-blue-600 mt-1">{{ $totalProvinsi }}</h3>
            </div>

            <div class="p-5 bg-white rounded-xl shadow border border-gray-100">
                <p class="text-sm text-gray-500">Total Kabupaten</p>
                <h3 class="text-3xl font-bold text-orange-600 mt-1">{{ $totalKabupaten }}</h3>
            </div>

        </div>


        {{-- ======= Rapat Terdekat ======= --}}
        <div class="mt-5">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">Kawasan Transmigrasi</h3>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="w-full flex mb-4">
                <div class="relative w-full sm:w-2/3 lg:w-1/2">

                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m1.6-5.4A7 7 0 1 1 5 5a7 7 0 0 1 13.25 6.25Z" />
                        </svg>
                    </span>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kawasan, provinsi, atau kabupaten..."
                        class="w-full py-3 pl-11 pr-28 bg-white border border-gray-300 rounded-xl 
                        text-gray-900 text-base shadow-sm
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                        transition-all duration-200 placeholder-gray-500"
                    />
                </div>
            </form>

            <div class="overflow-x-auto bg-white p-4 rounded-xl shadow border border-gray-100">
                <table class="min-w-full text-sm border-collapse">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr class="bg-gray-700 text-white text-left">
                            <th class="px-4 py-3 border">No</th>
                            <th class="px-4 py-3 border">Provinsi</th>
                            <th class="px-4 py-3 border">Kabupaten</th>
                            <th class="px-4 py-3 border">Kawasan Transmigrasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kawasanList as $index => $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 border">
                                    {{-- Menggunakan firstItem agar nomor berlanjut di halaman berikutnya --}}
                                    {{ $kawasanList->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-3 border">{{ $item->provinsi->nama_provinsi }}</td>
                                <td class="px-4 py-3 border">{{ $item->kabupaten->nama_kabupaten }}</td>
                                <td class="px-4 py-3 border">
                                    <button 
                                        class="detail-btn text-blue-600 hover:underline"
                                        data-id="{{ $item->id }}" data-url="{{ route('getDetailDashboard', $item->id) }}">
                                        {{ $item->nama_kawasan }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                        @if ($kawasanList->count() === 0)
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-400">
                                    Belum ada data kawasan.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                
                {{-- Pagination Links --}}
                <div class="mt-4 px-4 flex justify-end">
                    {{ $kawasanList->links() }}
                </div>
            </div>
        </div>

    </div>
    <div id="modal-detail" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="fixed inset-0 bg-gray-900/80 transition-opacity" onclick="closeModal()"></div>

    <div class="relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        <div class="px-8 py-5 bg-[#0b3a68] flex justify-between items-center shrink-0 z-10">
            <div>
                <h2 class="text-xl font-bold text-white tracking-wide" id="modal-title">
                    Detail Kawasan Transmigrasi
                </h2>
                <p class="text-xs text-blue-200 mt-1">Data Lengkap Kawasan Transmigrasi Terpadu</p>
            </div>
            <button onclick="closeModal()" class="text-white/70 hover:text-white hover:bg-white/10 rounded-full p-2 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-8 overflow-y-auto custom-scrollbar bg-gray-50/50 space-y-8 overscroll-contain will-change-scroll">
            
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 uppercase border-b pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Identitas & Lokasi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Nama Kawasan</p>
                        <p id="detail-nama_kawasan" class="text-base font-bold text-gray-900 mt-1">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Kode Kawasan</p>
                        <p id="detail-kode_kawasan" class="text-base font-mono font-medium text-blue-700 mt-1 bg-blue-50 inline-block px-2 rounded">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Nama Lokasi</p>
                        <p id="detail-nama_lokasi" class="text-base font-medium text-gray-900 mt-1">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Kode Lokasi</p>
                        <p id="detail-kode_lokasi" class="text-base font-mono font-medium text-gray-600 mt-1">-</p>
                    </div>
                    <div class="lg:col-span-2">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Provinsi</p>
                        <p id="detail-provinsi" class="text-base font-medium text-gray-900 mt-1">-</p>
                    </div>
                    <div class="lg:col-span-2">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Kabupaten</p>
                        <p id="detail-kabupaten" class="text-base font-medium text-gray-900 mt-1">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 uppercase border-b pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Demografi & Sosial
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Jumlah Desa</p>
                        <p class="text-lg font-bold text-gray-800"><span id="detail-jumlah_desa">-</span> <span class="text-xs font-normal text-gray-500">Desa</span></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Penduduk</p>
                        <p class="text-lg font-bold text-gray-800"><span id="detail-jumlah_penduduk">-</span> <span class="text-xs font-normal text-gray-500">Jiwa</span></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Transmigran (Intrans)</p>
                        <p class="text-lg font-bold text-gray-800"><span id="detail-intrans">-</span> <span class="text-xs font-normal text-gray-500">KK</span></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 uppercase border-b pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Ekonomi & Potensi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Produk Unggulan</p>
                        <div id="detail-produk_unggulan" class="text-sm font-medium text-gray-800 bg-yellow-50 border border-yellow-100 p-2 rounded">-</div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Potensi Kawasan</p>
                        <div id="detail-potensi" class="text-sm font-medium text-gray-800 bg-blue-50 border border-blue-100 p-2 rounded">-</div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Pendapatan Perkapita</p>
                        <p class="text-sm font-bold text-gray-800">Rp <span id="detail-pendapatan_perkapita">-</span></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nilai Investasi</p>
                        <p class="text-sm font-bold text-gray-800">Rp <span id="detail-investasi">-</span></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kegiatan Kolaborasi</p>
                        <p id="detail-keg_kolaborasi" class="text-sm font-medium text-gray-800">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-orange-50/50 p-6 rounded-xl border border-orange-100 shadow-sm">
                <h3 class="text-sm font-bold text-orange-900 uppercase border-b border-orange-200 pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Status Perkembangan Desa
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 text-center">
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-orange-100">
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Mandiri</p>
                        <p id="detail-desa_mandiri" class="text-2xl font-bold text-blue-600">-</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-orange-100">
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Maju</p>
                        <p id="detail-desa_maju" class="text-2xl font-bold text-green-600">-</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-orange-100">
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Berkembang</p>
                        <p id="detail-desa_berkembang" class="text-2xl font-bold text-yellow-600">-</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-orange-100">
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Tertinggal</p>
                        <p id="detail-desa_tertinggal" class="text-2xl font-bold text-orange-600">-</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-orange-100">
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Sgt Tertinggal</p>
                        <p id="detail-desa_sangat_tertinggal" class="text-2xl font-bold text-red-600">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 uppercase border-b pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Indikator TEP
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-purple-50 p-4 rounded-lg flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Indikator Kelembagaan</span>
                        <span id="detail-kelembagaan" class="text-lg font-bold text-purple-700 bg-white px-3 py-1 rounded shadow-sm">-</span>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Indikator Ekonomi</span>
                        <span id="detail-ekonomi" class="text-lg font-bold text-blue-700 bg-white px-3 py-1 rounded shadow-sm">-</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="px-8 py-4 bg-gray-50 border-t flex justify-end shrink-0">
            <button onclick="closeModal()" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-colors">
                Tutup Tampilan
            </button>
        </div>
    </div>
</div>

    <script>
    // Mencegah back button browser keluar dari dashboard
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.pushState(null, null, location.href);
    };

    function openModal() {
        const modal = document.getElementById('modal-detail');
        modal.classList.remove('hidden');
        // Prevent body scroll when modal open
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('modal-detail');
        modal.classList.add('hidden');
        // Restore body scroll
        document.body.style.overflow = 'auto';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    // ... kode lainnya ...

    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            
            // PENTING: Pastikan route ini benar di web.php Anda
            fetch(`/admin/dashboard/detail/${id}`) 
                .then(res => res.json())
                .then(result => {
                    const k = result.kawasan;
                    const tep = result.tep || [];

                    const setText = (id, val) => {
                        const el = document.getElementById(id);
                        if(el) el.innerText = (val !== null && val !== undefined && val !== '') ? val : '-';
                    };

                    // 1. Identitas
                    setText('detail-nama_kawasan', k.nama_kawasan);
                    setText('detail-kode_kawasan', k.kode_kawasan);
                    setText('detail-nama_lokasi', k.nama_lokasi);
                    setText('detail-kode_lokasi', k.kode_lokasi);
                    setText('detail-provinsi', k.provinsi?.nama_provinsi);
                    setText('detail-kabupaten', k.kabupaten?.nama_kabupaten);

                    // 2. Demografi
                    setText('detail-jumlah_desa', k.jumlah_desa);
                    setText('detail-jumlah_penduduk', k.jumlah_penduduk);
                    setText('detail-intrans', k.intrans);

                    // 3. Ekonomi & Potensi
                    setText('detail-produk_unggulan', k.produk_unggulan);
                    setText('detail-potensi', k.potensi); // <-- Pastikan ini ada
                    setText('detail-pendapatan_perkapita', k.pendapatan_perkapita);
                    setText('detail-investasi', k.investasi);
                    setText('detail-keg_kolaborasi', k.keg_kolaborasi);

                    // 4. Status Desa (5 Kolom Baru)
                    setText('detail-desa_mandiri', k.desa_mandiri ?? 0);
                    setText('detail-desa_maju', k.desa_maju ?? 0);
                    setText('detail-desa_berkembang', k.desa_berkembang ?? 0);
                    setText('detail-desa_tertinggal', k.desa_tertinggal ?? 0);
                    setText('detail-desa_sangat_tertinggal', k.desa_sangat_tertinggal ?? 0);

                    // 5. TEP
                    const kelem = tep.find(t => t.indikator?.nama_indikator === 'Kelembagaan');
                    const ekon = tep.find(t => t.indikator?.nama_indikator === 'Ekonomi');
                    setText('detail-kelembagaan', kelem?.nilai);
                    setText('detail-ekonomi', ekon?.nilai);

                    openModal();
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Gagal memuat detail data.');
                });
        });
    });
</script>
</body>

</html>