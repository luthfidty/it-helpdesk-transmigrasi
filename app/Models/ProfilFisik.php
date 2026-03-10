<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProfilFisik extends Model
{
    use HasFactory;

    protected $table = 'profil_fisik';
    protected $primaryKey = 'id';

    protected $fillable = [
        'rkt',
        'rtsp',
        'rskp',
        'status_lahan_hpl',
        'status_lahan_shm',
        'jarak_ke_provinsi',
        'jarak_ke_kabupaten',
        'jarak_ke_kecamatan',
        'konektivitas_digital',
    ];
}

