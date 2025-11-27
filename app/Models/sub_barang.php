<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sub_barang extends Model
{
    protected $table = 'sub_barang';

    protected $guarded = ['id'];

    public function barang()
    {
        return $this->belongsTo(barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
