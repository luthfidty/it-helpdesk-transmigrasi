<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class InfoKawasan extends Model
{
    use HasFactory;

    protected $table = 'info_kawasan';
    protected $primaryKey = 'id';
    

    protected $fillable = [
        'kawasan_id',
        'status_kawasan',
        'ipkt_2023',
        'jumlah_kecamatan',
        'jumlah_desa',
        'jumlah_sp_bina',
        'luas_kawasan_ha',
        'jumlah_penduduk',
        'dasar_penetapan',
        'kriteria',
        'potensi_daya_tampung_kk',
        'desa_mandiri',
        'desa_maju',
        'desa_berkembang',
        'desa_tertinggal',
        'desa_sangat_tertinggal',
    ];
        public function kawasan()
    {
        return $this->belongsTo(KawasanTransmigrasi::class, 'kawasan_id');
    }
}
