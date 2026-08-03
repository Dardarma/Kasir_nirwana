<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
            ->where('transaksi.jenis_transaksi', 'produksi')
            ->select([
                'transaksi_detail.id',
                'transaksi_detail.jumlah',
                'transaksi_detail.satuan',
            ])
            ->orderBy('transaksi_detail.id')
            ->each(function ($detail) {
                $jumlahGram = $this->convertToGram(
                    (float) $detail->jumlah,
                    (string) $detail->satuan
                );

                DB::table('transaksi_detail')
                    ->where('id', $detail->id)
                    ->update(['jumlah_gram' => $jumlahGram]);

                DB::table('stok_masuk')
                    ->where('transaksi_detail_id', $detail->id)
                    ->update(['qty_masuk' => $jumlahGram]);
            });
    }

    public function down(): void
    {
        // Nilai lama memakai faktor tetap 250 dan tidak aman untuk direkonstruksi.
    }

    private function convertToGram(float $jumlah, string $satuan): int
    {
        return (int) round(match (strtolower(trim($satuan))) {
            'kg', 'kilogram' => $jumlah * 1000,
            'gram', 'gr', 'g' => $jumlah,
            default => $jumlah,
        });
    }
};
