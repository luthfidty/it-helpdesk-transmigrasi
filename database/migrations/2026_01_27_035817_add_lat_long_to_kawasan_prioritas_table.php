<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
{
    Schema::table('kawasan_transmigrasi', function (Blueprint $table) {
        // Menambahkan kolom latitude dan longitude.
        // Tipe 'double' biasanya cukup untuk koordinat peta.
        // Kita set 'nullable' agar tidak error jika datanya kosong.
        $table->double('latitude')->nullable()->after('nama_kawasan');
        $table->double('longitude')->nullable()->after('latitude');
    });
}

public function down()
{
    Schema::table('kawasan_transmigrasi', function (Blueprint $table) {
        $table->dropColumn(['latitude', 'longitude']);
    });
}
};
