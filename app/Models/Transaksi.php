<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $guarded = ['id'];



    public static function generateCode(string $prefix): string
    {
        if ($prefix === 'AS') {
            $today = now();
            $date = $today->toDateString();
            $codeDate = $today->format('Ymd');

            return DB::transaction(function () use ($date, $codeDate) {
                DB::table('kasir_code_sequences')->insertOrIgnore([
                    'tanggal' => $date,
                    'last_number' => 0,
                ]);

                $sequence = DB::table('kasir_code_sequences')
                    ->where('tanggal', $date)
                    ->lockForUpdate()
                    ->first();
                $nextNumber = (int) $sequence->last_number + 1;

                if ($nextNumber > 999) {
                    throw new \RuntimeException('Nomor transaksi kasir hari ini sudah mencapai batas 999.');
                }

                DB::table('kasir_code_sequences')
                    ->where('tanggal', $date)
                    ->update(['last_number' => $nextNumber]);

                return 'AS' . $codeDate . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            });
        }

        return $prefix . now()->format('Ymd') . Str::ulid();
    }

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
