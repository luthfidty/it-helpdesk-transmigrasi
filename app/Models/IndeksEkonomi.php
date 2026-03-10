<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class IndeksEkonomi extends Model
{
    use HasFactory;

    protected $table = 'indeks_ekonomi';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kawasan_prioritas_id',
        'indeks_ekonomi',
        'bumdes',
        'hw_trans',
        'koperasi',
    ];
    public function kawasan()
    {
        return $this->belongsTo(KawasanPrioritas::class, 'kawasan_prioritas_id');
    }
}