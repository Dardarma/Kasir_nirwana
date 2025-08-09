<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Barang_controller;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ControllerStok;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ControllerPemakaian;

Route::get('/', function () {
    return view('home');
});

Route::get('/kasir',[KasirController::class, 'index'])->name('kasir.index');
Route::post('/kasir', [KasirController::class, 'store'])->name('kasir.store');
Route::get('/kasir/list', [KasirController::class, 'list'])->name('kasir.list');
Route::get('/kasir/{id}/detail', [KasirController::class, 'getDetail']);
Route::get('/kasir/stok-realtime/{barang_id?}', [KasirController::class, 'getStokRealtime'])->name('kasir.stok-realtime');
Route::put('/kasir/edit/{id}', [KasirController::class, 'editStatusPembayaran'])->name('kasir.update');
Route::put('/kasir/delete/{id}', [KasirController::class, 'destroy'])->name('kasir.destroy');


Route::get('/barang', [Barang_controller::class, 'index']);
Route::post('/barang', [Barang_controller::class, 'store'])->name('Barang_store');
Route::put('/barang/{id}', [Barang_controller::class, 'update'])->name('Barang_update');
Route::put('/barang/delete/{id}', [Barang_controller::class, 'destroy'])->name('Barang_destroy');

Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');
Route::get('/pembelian/list', [PembelianController::class, 'list'])->name('pembelian.list');
Route::get('/pembelian/{id}/detail', [PembelianController::class, 'getDetail']);
Route::put('/pembelian/edit/{id}', [PembelianController::class, 'editStatusPembayaran'])->name('pembelian.update');
Route::put('/pembelian/delete/{id}', [PembelianController::class, 'destroy'])->name('pembelian.destroy');

Route::get('/produksi', [ProduksiController::class, 'index']);
Route::post('/produksi', [ProduksiController::class, 'store'])->name('produksi.store');
Route::get('/produksi/list', [ProduksiController::class, 'list'])->name('produksi.list');
Route::get('/produksi/{id}/detail', [ProduksiController::class, 'getDetail']);
Route::put('/produksi/delete/{id}', [ProduksiController::class, 'destroy'])->name('produksi.destroy');


Route::get('/pemakaian', [ControllerPemakaian::class, 'index'])->name('pemakaian.index');
Route::post('/pemakaian', [ControllerPemakaian::class, 'store'])->name('pemakaian.store');
Route::get('/pemakaian/list', [ControllerPemakaian::class, 'list'])->name('pemakaian.list');
Route::get('/pemakaian/{id}/detail', [ControllerPemakaian::class, 'getDetail']);
Route::put('/pemakaian/delete/{id}', [ControllerPemakaian::class, 'destroy'])->name('pemakaian.destroy');
Route::get('/pemakaian/stok-realtime/{barang_id?}', [ControllerPemakaian::class, 'getStokRealtime'])->name('pemakaian.stok-realtime');
Route::get('/pemakaian/list', [ControllerPemakaian::class, 'list'])->name('pemakaian.list');
Route::get('/pemakaian/{id}/detail', [ControllerPemakaian::class, 'getDetail']);


Route::get('/report',function(){
    return view('report');
});

Route::get('/stok', [ControllerStok::class, 'index'])->name('stok.index');

Route::get('/laba-rugi', [ControllerStok::class, 'getLabaRugi'])->name('stok.laba_rugi');
