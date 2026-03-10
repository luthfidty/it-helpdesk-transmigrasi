<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Kabupaten extends Model
{
    use HasFactory;

    protected $table = 'kabupaten';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nama_kabupaten',
        'provinsi_id'
    ];

    public function provinsi()
{
    // Ini menghubungkan ID provinsi di tabel kabupaten ke tabel provinsi
    return $this->belongsTo(Provinsi::class, 'provinsi_id');
}

    public function kawasanTransmigrasi()
    {
        return $this->hasMany(KawasanTransmigrasi::class, 'kabupaten_id');
    }
}