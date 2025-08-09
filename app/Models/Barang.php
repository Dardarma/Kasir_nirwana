<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $guarded = ['id'];

    public function transaksi_detail()
    {
        return $this->hasmany(Transaksi_detail::class);
    }

    public function stok_masuk()
    {
        return $this->hasMany(Stok_masuk::class);
    }
    
    public function stok_keluar()
    {
        return $this->hasMany(Stok_keluar::class);
    }
}
