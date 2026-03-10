<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class EvaluasiItem extends Model
{
    use HasFactory;

    protected $table = 'evaluasi_item';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kategori_id',
        'kode_item',
        'nama_item',
    ];
    public function kategori()
    {
        return $this->belongsTo(EvaluasiKategori::class, 'kategori_id');
    }
}
