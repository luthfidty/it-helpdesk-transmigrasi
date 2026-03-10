<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class KawasanTransmigrasi extends Model
{
    use HasFactory;

    protected $table = 'kawasan_transmigrasi';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provinsi_id',
        'kabupaten_id',
        'nama_kawasan',
        'kode_kawasan',
        'nama_lokasi',
        'kode_lokasi',
        'jumlah_desa',
        'jumlah_penduduk',
        'intrans',
        'produk_unggulan', 
        'potensi',         
        'pendapatan_perkapita',
        'desa_mandiri',
        'desa_maju',
        'desa_berkembang',
        'desa_tertinggal',
        'desa_sangat_tertinggal',
        'keg_kolaborasi',
        'investasi',
        'latitude',  
        'longitude',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function tep()
    {
        return $this->hasMany(TepNilai::class, 'kawasan_id');
    }

    public function indikator()
    {
        return $this->hasMany(TepIndikator::class, 'kawasan_id');
    }
    public function dokumen()
    {
        return $this->hasMany(KawasanDokumen::class, 'kawasan_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'kawasan_id');
    }

        public function statusDesaKawasan()
    {
        return $this->hasOne(StatusDesaKawasan::class, 'kawasan_id', 'id');
    }


}

