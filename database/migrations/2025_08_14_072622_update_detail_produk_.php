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
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->foreignId('sub_barang_id')->nullable()->constrained('sub_barang')->onDelete('cascade')->after('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->dropForeign(['sub_barang_id']);
            $table->dropColumn('sub_barang_id');
        });
    }
};
