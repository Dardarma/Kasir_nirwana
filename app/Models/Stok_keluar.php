<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stok_keluar extends Model
{
    protected $table = 'stok_keluar';

    protected $guarded = ['id'];
    
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    
}
