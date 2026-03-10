<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class DokumentasiKawasan extends Model
{
    use HasFactory;

    protected $table = 'dokumentasi_kawasan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'file_path',
        'keterangan',
    ];
}
