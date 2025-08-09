<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi_detail extends Model
{
    protected $table = 'transaksi_detail';

    protected $guarded = ['id'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

}
