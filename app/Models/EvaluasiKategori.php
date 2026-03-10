<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EvaluasiKategori extends Model
{
    use HasFactory;

    protected $table = 'evaluasi_kategori';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
    ];

    public function items()
    {
        return $this->hasMany(EvaluasiItem::class, 'kategori_id')
                    ->orderBy('kode_item', 'ASC');
    }

}
