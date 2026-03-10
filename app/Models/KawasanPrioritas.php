<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class KawasanPrioritas extends Model
{
    use HasFactory;

    protected $table = 'kawasan_prioritas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provinsi_id',
        'kabupaten_id',
        'nama_kawasan',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }
    public function indeksInfra()
    {
        return $this->hasOne(IndeksInfra::class, 'kawasan_prioritas_id');
    }

    public function indeksSosial()
    {
        return $this->hasOne(IndeksSosial::class, 'kawasan_prioritas_id');
    }

    public function indeksEkonomi()
    {
        return $this->hasOne(IndeksEkonomi::class, 'kawasan_prioritas_id');
    }

    public function kawasanTransmigrasi()
    {
        return $this->belongsTo(KawasanTransmigrasi::class, 'kawasan_transmigrasi_id', 'id');
    }
}
