<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kawasan_transmigrasi', function (Blueprint $table) {
            // Kita gunakan tipe 'double' untuk koordinat peta
            // Kita set 'nullable' agar data lama yang belum punya koordinat tidak error
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
