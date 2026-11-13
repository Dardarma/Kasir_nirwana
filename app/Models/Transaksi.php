<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $guarded = ['id'];



    public function transaksi_detail()
    {
        return $this->hasMany(Transaksi_detail::class);
    }

    public function transaksiDetail()
    {
        return $this->hasMany(Transaksi_detail::class);
    }

    public function customerData(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
 
}
