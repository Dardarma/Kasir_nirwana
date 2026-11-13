<?php

return [
    'permissions' => [
        'dashboard.view' => [
            'name' => 'Lihat Dashboard',
            'description' => 'Membuka halaman utama aplikasi.',
        ],

        'barang.view' => ['name' => 'Lihat Barang', 'description' => 'Melihat daftar master barang.'],
        'barang.create' => ['name' => 'Tambah Barang', 'description' => 'Menambahkan master barang baru.'],
        'barang.update' => ['name' => 'Ubah Barang', 'description' => 'Mengubah data master barang.'],
        'barang.delete' => ['name' => 'Nonaktifkan Barang', 'description' => 'Menonaktifkan data master barang.'],

        'sub-barang.view' => ['name' => 'Lihat Sub Barang', 'description' => 'Melihat produk dan satuan jual customer.'],
        'sub-barang.create' => ['name' => 'Tambah Sub Barang', 'description' => 'Menambahkan produk atau satuan jual customer.'],
        'sub-barang.update' => ['name' => 'Ubah Sub Barang', 'description' => 'Mengubah produk atau satuan jual customer.'],
        'sub-barang.delete' => ['name' => 'Nonaktifkan Sub Barang', 'description' => 'Menonaktifkan produk atau satuan jual customer.'],

        'customers.view' => ['name' => 'Lihat Customer', 'description' => 'Melihat daftar customer.'],
        'customers.create' => ['name' => 'Tambah Customer', 'description' => 'Menambahkan customer baru.'],
        'customers.update' => ['name' => 'Ubah Customer', 'description' => 'Mengubah data customer.'],
        'customers.delete' => ['name' => 'Nonaktifkan Customer', 'description' => 'Menonaktifkan customer.'],

        'users.view' => ['name' => 'Lihat User', 'description' => 'Melihat daftar akun user.'],
        'users.create' => ['name' => 'Tambah User', 'description' => 'Membuat akun user baru.'],
        'users.update' => ['name' => 'Ubah User', 'description' => 'Mengubah akun, status, dan role user.'],
        'users.delete' => ['name' => 'Nonaktifkan User', 'description' => 'Menonaktifkan akun user.'],
        'roles.manage' => ['name' => 'Kelola Role & Permission', 'description' => 'Mengatur role dan daftar permission.'],

        'kasir.view' => ['name' => 'Lihat Kasir', 'description' => 'Membuka kasir dan melihat riwayat penjualan.'],
        'kasir.create' => ['name' => 'Buat Transaksi Kasir', 'description' => 'Menyimpan transaksi penjualan baru.'],
        'kasir.update' => ['name' => 'Ubah Transaksi Kasir', 'description' => 'Mengubah status pembayaran penjualan.'],
        'kasir.delete' => ['name' => 'Hapus Transaksi Kasir', 'description' => 'Membatalkan atau menghapus transaksi penjualan.'],

        'pembelian.view' => ['name' => 'Lihat Pembelian', 'description' => 'Membuka pembelian dan melihat riwayatnya.'],
        'pembelian.create' => ['name' => 'Buat Pembelian', 'description' => 'Menyimpan transaksi pembelian baru.'],
        'pembelian.update' => ['name' => 'Ubah Pembelian', 'description' => 'Mengubah status pembayaran pembelian.'],
        'pembelian.delete' => ['name' => 'Hapus Pembelian', 'description' => 'Membatalkan atau menghapus transaksi pembelian.'],

        'produksi.view' => ['name' => 'Lihat Produksi', 'description' => 'Membuka produksi dan melihat riwayatnya.'],
        'produksi.create' => ['name' => 'Buat Produksi', 'description' => 'Menyimpan hasil produksi baru.'],
        'produksi.delete' => ['name' => 'Hapus Produksi', 'description' => 'Membatalkan atau menghapus transaksi produksi.'],

        'pemakaian.view' => ['name' => 'Lihat Pemakaian', 'description' => 'Membuka pemakaian barang dan melihat riwayatnya.'],
        'pemakaian.create' => ['name' => 'Buat Pemakaian', 'description' => 'Menyimpan pemakaian barang baru.'],
        'pemakaian.delete' => ['name' => 'Hapus Pemakaian', 'description' => 'Membatalkan atau menghapus transaksi pemakaian.'],

        'reports.view' => ['name' => 'Lihat Menu Laporan', 'description' => 'Membuka halaman pilihan laporan.'],
        'stock.view' => ['name' => 'Lihat Stok', 'description' => 'Melihat laporan dan posisi stok barang.'],
        'laba-rugi.view' => ['name' => 'Lihat Laba Rugi', 'description' => 'Melihat laporan laba rugi.'],
    ],
];
