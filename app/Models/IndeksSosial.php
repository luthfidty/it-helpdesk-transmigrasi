<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class IndeksSosial extends Model
{
    use HasFactory;

    protected $table = 'indeks_sosial';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kawasan_prioritas_id',
        'indeks_sosial',
        'lembaga',
        'pemberdayaan',
        'gapoktan',
        'pokdarwis',
        'pokdakan',
        'poklahsar',
    ];
    public function kawasan()
    {
        return $this->belongsTo(KawasanPrioritas::class, 'kawasan_prioritas_id');
    }
}