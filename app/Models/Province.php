<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    // Arahkan ke tabel 'provinces' milik IndoRegion (bukan 'provinsi')
    protected $table = 'provinces';
    
    // Matikan timestamps karena tabel bawaan IndoRegion biasanya tidak punya created_at/updated_at
    public $timestamps = false;

    // Kolomnya 'name' (bukan 'nama_provinsi')
    protected $fillable = ['id', 'name'];
}