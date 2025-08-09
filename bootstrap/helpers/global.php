<?php

use App\Models\Barang;
use Illuminate\Support\Facades\DB;

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('get_barang_jadi')) {
    function get_barang_jadi()
    {
        return Barang::where('status', 'aktif')
            ->where('kategori', 'jadi')
            ->where('sub_kategori', '!=', 'penolong/alat')
            ->get();
    }
}

if (!function_exists('get_stok')) {
    function get_stok()
    {
        // Clear any potential cache and get fresh data
        return Barang::select([
            'id',
            'nama_barang', // Tambahkan nama barang untuk debugging
            // Subquery untuk stok akhir dengan fresh data
            DB::raw('(
                SELECT COALESCE(SUM(qty_masuk), 0) 
                FROM stok_masuk 
                WHERE barang_id = barang.id
            ) - (
                SELECT COALESCE(SUM(qty_keluar), 0) 
                FROM stok_keluar 
                WHERE barang_id = barang.id
            ) as stok')
        ])
        ->where('status', 'aktif') // Hanya barang aktif
        ->where('sub_kategori', '!=', 'penolong/alat') // Kecualikan barang penolong/alat
        ->orderBy('id')
        ->get()
        ->fresh(); // Force fresh data dari database
    }
}

if (!function_exists('get_stok_by_id')) {
    function get_stok_by_id($barang_id)
    {
        // Helper untuk mendapatkan stok real-time berdasarkan ID barang
        $stok_masuk = DB::table('stok_masuk')
            ->where('barang_id', $barang_id)
            ->sum('qty_masuk');
            
        $stok_keluar = DB::table('stok_keluar')
            ->where('barang_id', $barang_id)
            ->sum('qty_keluar');
            
        return $stok_masuk - $stok_keluar;
    }
}

if (!function_exists('get_barang_produksi')) {
    function get_barang_produksi()
    {
        return Barang::where('status', 'aktif')
            ->where('kategori', 'produksi')
            ->where('sub_kategori', '!=', 'penolong/alat')
            ->get();
    }
}

