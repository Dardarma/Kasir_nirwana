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
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->foreignId('sub_barang_id')->nullable()->constrained('sub_barang')->nullOnDelete();
            $table->decimal('jumlah', 20, 2);
            $table->decimal('jumlah_gram', 20, 2);
            $table->decimal('faktor_satuan', 15, 6)->nullable();
            $table->string('satuan_stok', 50)->nullable();
            $table->integer('keterangan')->nullable();
            $table->bigInteger('subtotal')->nullable();
            $table->string('nama_barang');
            $table->string('satuan');
            $table->string('kategori_barang');
            $table->integer('harga_satuan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};
