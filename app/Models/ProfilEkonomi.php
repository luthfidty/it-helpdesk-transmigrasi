<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProfilEkonomi extends Model
{
    use HasFactory;

    protected $table = 'profil_ekonomi';
    protected $primaryKey = 'id';

    protected $fillable = [
        'pendapatan_perkapita',
        'mata_pencaharian',
        'jumlah_petani',
        'sarana_ekonomi_pasar',
        'sarana_ekonomi_kios',
        'bumdes',
        'lembaga_lain',
        'kerjasama',
        'produktivitas_produk',
    ];
}

