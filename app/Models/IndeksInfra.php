<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class IndeksInfra extends Model
{
    use HasFactory;

    protected $table = 'indeks_infra';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kawasan_prioritas_id',
        'indeks_infrastruktur',
        'transportasi',
        'telekomunikasi',
        'air_bersih',
        'kesehatan',
        'listrik',
        'sarpras_komoditas',
    ];
    public function kawasan()
    {
        return $this->belongsTo(KawasanPrioritas::class, 'kawasan_prioritas_id');
    }
}
