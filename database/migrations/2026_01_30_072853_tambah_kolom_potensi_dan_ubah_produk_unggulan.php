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
            // 1. Tambah kolom 'potensi' dengan tipe TEXT (bisa menampung paragraf panjang)
            // Kita taruh setelah kolom 'produk_unggulan'
            $table->text('potensi')->nullable()->after('produk_unggulan');

            // 2. Ubah kolom 'produk_unggulan' yang tadinya VARCHAR (string) menjadi TEXT
            // Agar bisa menampung deskripsi produk yang lebih panjang
            $table->text('produk_unggulan')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kawasan_transmigrasi', function (Blueprint $table) {
            // Rollback: Hapus kolom potensi
            $table->dropColumn('potensi');

            // Rollback: Kembalikan produk_unggulan jadi string biasa (255 karakter)
            $table->string('produk_unggulan', 255)->change();
        });
    }
};