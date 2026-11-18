<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SUB_KATEGORI = ['bahan_baku', 'penolong/alat', 'barang_jadi', 'produk_jadi'];

    public function up(): void
    {
        $this->changeSubKategori([...self::SUB_KATEGORI, 'non_stok']);
        Schema::table('sub_barang', function (Blueprint $table) {
            $table->integer('harga')->nullable()->change();
            if (DB::getDriverName() === 'sqlite') {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->change();
            }
        });
    }

    public function down(): void
    {
        if (DB::table('barang')->where('sub_kategori', 'non_stok')->exists()
            || DB::table('sub_barang')->whereNull('harga')->exists()) {
            throw new RuntimeException('Rollback membutuhkan sub kategori dan harga yang kompatibel dengan skema lama.');
        }

        Schema::table('sub_barang', function (Blueprint $table) {
            $table->integer('harga')->change();
            if (DB::getDriverName() === 'sqlite') {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->change();
            }
        });
        $this->changeSubKategori(self::SUB_KATEGORI);
    }

    private function changeSubKategori(array $values): void
    {
        Schema::table('barang', function (Blueprint $table) use ($values) {
            $table->enum('sub_kategori', $values)->change();
            if (DB::getDriverName() === 'sqlite') {
                $table->enum('kategori', ['produksi', 'jadi'])->change();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->change();
            }
        });
    }
};
