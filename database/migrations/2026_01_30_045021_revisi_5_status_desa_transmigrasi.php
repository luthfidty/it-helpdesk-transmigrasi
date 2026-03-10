<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kawasan_transmigrasi', function (Blueprint $table) {
            // 1. Hapus kolom lama (sesuaikan nama kolom lama Anda)
            // Cek dulu apakah kolomnya ada agar tidak error
            if (Schema::hasColumn('kawasan_transmigrasi', 'desa_maju_mandiri')) {
                $table->dropColumn('desa_maju_mandiri');
            }

            // 2. Tambahkan 5 Kolom Status Desa (Nullable/None)
            // Urutan penempatan (after) disesuaikan agar rapi di database
            $table->integer('desa_mandiri')->nullable()->after('pendapatan_perkapita');
            $table->integer('desa_maju')->nullable()->after('desa_mandiri');
            $table->integer('desa_berkembang')->nullable()->after('desa_maju');
            $table->integer('desa_tertinggal')->nullable()->after('desa_berkembang');
            $table->integer('desa_sangat_tertinggal')->nullable()->after('desa_tertinggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kawasan_transmigrasi', function (Blueprint $table) {
            // Hapus 5 kolom baru jika di-rollback
            $table->dropColumn([
                'desa_mandiri', 
                'desa_maju', 
                'desa_berkembang', 
                'desa_tertinggal', 
                'desa_sangat_tertinggal'
            ]);

            // Kembalikan kolom lama
            $table->integer('desa_maju_mandiri')->nullable()->after('pendapatan_perkapita');
        });
    }
};