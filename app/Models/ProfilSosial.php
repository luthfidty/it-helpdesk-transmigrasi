<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProfilSosial extends Model
{
    use HasFactory;

    protected $table = 'profil_sosial';
    protected $primaryKey = 'id';

    protected $fillable = [
        'usia_produktif',
        'usia_kurang_15',
        'usia_lebih_65',
        'jumlah_sd',
        'jumlah_smp',
        'jumlah_sma',
        'tempat_praktik',
        'puskesmas',
        'pustu',
    ];
}
