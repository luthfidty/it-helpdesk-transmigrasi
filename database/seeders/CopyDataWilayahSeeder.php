<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CopyDataWilayahSeeder extends Seeder
{
    public function run()
    {
        // Matikan pengecekan Foreign Key sementara agar tidak error urutan insert
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. PINDAHKAN PROVINSI
        $sourceProvinces = DB::table('provinces')->get(); 

        foreach ($sourceProvinces as $p) {
            // Cek dulu biar gak duplikat
            $exists = DB::table('provinsi')->where('id', $p->id)->exists();
            
            if (!$exists) {
                DB::table('provinsi')->insert([
                    'id' => $p->id,
                    'nama_provinsi' => $p->name, 
                    // 'created_at' dan 'updated_at' DIHAPUS
                ]);
            }
        }
        $this->command->info('Data Provinsi berhasil dipindah!');

        // 2. PINDAHKAN KABUPATEN
        $sourceRegencies = DB::table('regencies')->get(); 

        foreach ($sourceRegencies as $r) {
            $exists = DB::table('kabupaten')->where('id', $r->id)->exists();

            if (!$exists) {
                DB::table('kabupaten')->insert([
                    'id' => $r->id,
                    'provinsi_id' => $r->province_id, 
                    'nama_kabupaten' => $r->name,     
                    // 'created_at' dan 'updated_at' DIHAPUS
                ]);
            }
        }
        
        // Nyalakan lagi pengecekan Foreign Key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Data Kabupaten berhasil dipindah!');
    }
}