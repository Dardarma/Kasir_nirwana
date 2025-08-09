<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stok_masuk extends Model
{
    protected $table = 'stok_masuk';

    protected $guarded = ['id'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function transaksiDetail()
    {
        return $this->belongsTo(Transaksi_detail::class);
    }
}
