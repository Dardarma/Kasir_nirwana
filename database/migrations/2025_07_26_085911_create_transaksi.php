<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer')->nullable();
            $table->string('kode_transaksi')->unique();
            $table->enum('status',['aktif','dibatalkan']);
            $table->enum('status_pembayaran',['lunas','belum_lunas'])->nullable();
            $table->bigInteger('customer_bayar')->nullable();
            $table->enum('metode_pembayaran',['tunai','transfer','qris'])->nullable();
            $table->date('tanggal_pembayaran')->nullable();
            $table->enum('jenis_transaksi',['pembelian','penjualan','produksi','pemakaian']);
            $table->bigInteger('total_bayar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
