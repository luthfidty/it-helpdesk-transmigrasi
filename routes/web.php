<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SesiController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserKawasanController;

Route::middleware(['web'])->group(function () {
            
    Route::get('/', [SesiController::class, 'welcome'])->name('welcome');
    Route::get('/kawasan/{id}/detail', [SesiController::class, 'detailKawasan'])->name('kawasan.detail');
    Route::post('/logout', [SesiController::class, 'logout'])->name('logout');

    Route::get('/detailPeta/{id}', [SesiController::class, 'detailPeta'])->name('detailPeta');
    
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SesiController::class, 'getLogin'])->name('getLogin');
        Route::post('/login', [SesiController::class, 'login'])->name('login');

        Route::get('/register', [SesiController::class, 'getRegister'])->name('register.form');
        Route::post('/register', [SesiController::class, 'register'])->name('register');
    });

// ==========================================================
    // 1. AREA KHUSUS SUPER ADMIN (Hanya Role 'admin')
    // ==========================================================
    // Fitur: Manajemen Akun & Reset Password User
    Route::middleware(['akses:admin', 'prevent-back-history'])->group(function () {
        
        Route::post('/admin/storeTambahAkun', [UsersController::class, 'storeTambahAkun'])->name('storeTambahAkun');
        Route::get('/admin/dataAkun', [UsersController::class, 'getDaftarAkun'])->name('getDaftarAkun');
        Route::put('/admin/updateAkun/{id}', [UsersController::class, 'updateAkun'])->name('updateAkun');
        Route::delete('/admin/deleteAkun/{id}', [UsersController::class, 'deleteAkun'])->name('deleteAkun');
        Route::get('/admin/dataAkun/search', [UsersController::class, 'searchAkun'])->name('searchAkun');
        
        // Reset Password User Kawasan
        Route::get('/admin/users', [UsersController::class, 'index'])->name('admin.users');
        Route::put('/admin/users/{id}/reset-password', [UsersController::class, 'resetPassword'])->name('admin.users.reset-password');
    });

    // ==========================================================
    // 2. AREA OPERASIONAL (Bisa 'admin' DAN 'admin_biasa')
    // ==========================================================
    // Fitur: Dashboard, Kawasan, TEP, Evaluasi, Profil, Indeks
    Route::middleware(['akses:admin,admin_biasa', 'prevent-back-history'])->group(function () {
        
        // Dashboard
        Route::get('/admin/dashboard', [UsersController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/dashboard/detail/{id}', [UsersController::class, 'getDetailDashboard'])->name('getDetailDashboard');

        // Evaluasi Kategori
        Route::get('/admin/evaluasiKategori', [UsersController::class, 'evaluasiKategori'])->name('evaluasiKategori');
        Route::post('/admin/store-step1', [UsersController::class, 'storeStep1'])->name('store-step1');
        Route::post('/admin/store-step2', [UsersController::class, 'storeStep2'])->name('store-step2');
        Route::get('/admin/evaluasiKategori/next-kode/{id}', [UsersController::class, 'getNextKode'])->name('getNextKode');

        // TEP
        Route::get('/admin/daftarTEP', [UsersController::class, 'getDaftarTEP'])->name('getDaftarTEP');
        Route::get('/admin/listTEP', [UsersController::class, 'listTEP'])->name('listTEP');
        Route::post('/admin/storeTEP', [UsersController::class, 'storeTEP'])->name('storeTEP');
        Route::put('/admin/updateTEP/{id}', [UsersController::class, 'updateTEP'])->name('updateTEP');
        Route::delete('/admin/deleteTEP/{id}', [UsersController::class, 'deleteTEP'])->name('deleteTEP');        

        // Data Transmigrasi
        Route::get('/admin/dataTransmigrasi', [UsersController::class, 'getDataTransmigrasi'])->name('getDataTransmigrasi');
        Route::post('/admin/storeDataKawasan', [UsersController::class, 'storeDataKawasan'])->name('storeDataKawasan');
        Route::post('/admin/dataTransmigrasi/store-step2', [UsersController::class, 'storeDataTEP'])->name('storeDataTEP');
        Route::get('/admin/get-kabupaten/{provinsi_id}', [UsersController::class, 'getKabupaten'])->name('getKabupaten');
        Route::get('/admin/dataTransmigrasi/detail/{id}', [UsersController::class, 'getDetail'])->name('getDetail');
        Route::get('/admin/dataTransmigrasi/editKawasan/{id}', [UsersController::class, 'editKawasan'])->name('editKawasan');
        Route::put('/admin/dataTransmigrasi/updateKawasan/{id}', [UsersController::class, 'updateKawasan'])->name('updateKawasan');
        Route::put('/admin/dataTransmigrasi/updateTEP/{id}', [UsersController::class, 'updateDataTEP'])->name('updateDataTEP');
        Route::delete('/admin/dataTransmigrasi/deleteKawasan/{id}', [UsersController::class, 'deleteKawasan'])->name('deleteKawasan');    

        // Upload Evaluasi & PDF
        Route::get('/admin/uploadEvaluasi', [UsersController::class, 'uploadEvaluasi'])->name('uploadEvaluasi');
        Route::post('/admin/uploadPDF/{id}', [UsersController::class, 'uploadPDF'])->name('uploadPDF');
        Route::get('/admin/previewPDF/{id}', [UsersController::class, 'previewPDF'])->name('previewPDF');
        Route::put('/admin/uploadEvaluasi/updatePDF/{id}', [UsersController::class, 'updatePDF'])->name('updatePDF');
        Route::delete('/admin/uploadEvaluasi/deletePDF/{id}', [UsersController::class, 'deletePDF'])->name('deletePDF');
        Route::delete('/delete-pdf/{id}', [UsersController::class, 'deletePDF'])->name('delete.pdf');

        // Profil Kawasan
        Route::get('/admin/profilKawasan', [UsersController::class, 'profilKawasan'])->name('profilKawasan');
        Route::post('/admin/storeProfil', [UsersController::class, 'storeProfil'])->name('storeProfil');
        Route::get('/kabupaten/{provinsi}', [UsersController::class, 'kabupaten'])->name('kabupaten');
        Route::get('/kawasan/{kabupaten}', [UsersController::class, 'kawasan'])->name('kawasan');
        Route::get('/admin/profilKawasan/editProfil/{id}', [UsersController::class, 'editProfil'])->name('editProfil');
        Route::put('/admin/profilKawasan/updateProfil/{id}', [UsersController::class, 'updateProfil'])->name('updateProfil');
        Route::get('/admin/profil-kawasan/detail/{id}', [UserKawasanController::class, 'show'])->name('admin.detailProfilKawasan');
        Route::delete('/admin/profilKawasan/delete/{id}', [UserKawasanController::class, 'destroyProfil'])->name('deleteProfil');

        // Kawasan Prioritas & Indeks
        Route::get('/admin/kawasanPrioritas', [UsersController::class, 'kawasanPrioritas'])->name('kawasanPrioritas');
        Route::post('/admin/storeKawasanPrioritas', [UsersController::class, 'storeKawasanPrioritas'])->name('storeKawasanPrioritas');
        
        // Indeks Infra
        Route::get('/admin/indeksInfra', [UsersController::class, 'indeksInfra'])->name('indeksInfra');
        Route::get('/admin/dataIndeksInfra', [UsersController::class, 'dataIndeksInfra'])->name('dataIndeksInfra');
        Route::post('/admin/storeIndeksInfra', [UsersController::class, 'storeIndeksInfra'])->name('storeIndeksInfra');
        
        // Indeks Ekonomi
        Route::get('/admin/indeksEkonomi', [UsersController::class, 'indeksEkonomi'])->name('indeksEkonomi');
        Route::get('/admin/dataIndeksEkonomi', [UsersController::class, 'dataIndeksEkonomi'])->name('dataIndeksEkonomi');
        Route::post('/admin/storeIndeksEkonomi', [UsersController::class, 'storeIndeksEkonomi'])->name('storeIndeksEkonomi');
        
        // Indeks Sosial
        Route::get('/admin/indeksSosial', [UsersController::class, 'indeksSosial'])->name('indeksSosial');
        Route::get('/admin/dataIndeksSosial', [UsersController::class, 'dataIndeksSosial'])->name('dataIndeksSosial');
        Route::post('/admin/storeIndeksSosial', [UsersController::class, 'storeIndeksSosial'])->name('storeIndeksSosial');
    });
    
    // ==========================================================
    // AREA USER (Ditambahkan prevent-back-history)
    // ==========================================================
    Route::middleware(['akses:user_kawasan', 'prevent-back-history'])->group(function () {

        Route::get('/user/force-password', [SesiController::class, 'forcePassword'])->name('user.forcePassword');
        Route::post('/user/force-password', [SesiController::class, 'updatePassword'])->name('user.password.force.update');

        Route::get('/user/dashboard', [UserKawasanController::class, 'dashboardKawasan'])->name('user.dashboard');
        Route::get('/user/uploadEval', [UserKawasanController::class, 'uploadEval'])->name('uploadEval');
        Route::post('/user/uploadFile/{id}', [UserKawasanController::class, 'uploadFile'])->name('uploadFile');
        Route::get('/user/previewFile/{id}', [UserKawasanController::class, 'previewFile'])->name('previewFile');
        Route::put('/user/uploadEval/updateFile/{id}', [UserKawasanController::class, 'updateFile'])->name('updateFile');
        Route::delete('/user/uploadEval/deleteFile/{id}', [UserKawasanController::class, 'deleteFile'])->name('deleteFile');

        Route::get('/user/dataKawasan', [UserKawasanController::class, 'getDataKawasan'])->name('getDataKawasan');
        Route::post('/user/storeKawasan', [UserKawasanController::class, 'storeKawasan'])->name('storeKawasan');
        Route::post('/user/dataKawasan/store-step2', [UserKawasanController::class, 'storeTEP'])->name('storeTEP');
        Route::get('/get-kabupaten/{provinsi_id}', [UserKawasanController::class, 'getListKabupaten'])->name('getListKabupaten');
        Route::get('/user/dataKawasan/editDataKawasan/{id}', [UserKawasanController::class, 'editDataKawasan'])->name('editDataKawasan');
        Route::put('/user/dataKawasan/updateDataKawasan/{id}', [UserKawasanController::class, 'updateDataKawasan'])->name('updateDataKawasan');
        Route::put('/user/dataKawasan/updateTEP/{id}', [UserKawasanController::class, 'updateTEP'])->name('updateTEP');
    });

});