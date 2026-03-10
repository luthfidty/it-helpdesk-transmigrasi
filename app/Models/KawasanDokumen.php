<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KawasanDokumen extends Model
{
    use HasFactory;

    protected $table = 'kawasan_dokumen';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kawasan_id',
        'nama_dokumen',
        'path_file',
    ];

    public function kawasan()
    {
        return $this->belongsTo(KawasanTransmigrasi::class, 'kawasan_id');
    }

}
