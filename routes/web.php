<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Barang_controller;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ControllerStok;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ControllerPemakaian;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ControllerSub_barang;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users.index');
Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])
    ->middleware('permission:users.update')->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])
    ->middleware('permission:users.delete')->name('users.destroy');
Route::resource('roles', RoleController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('permission:roles.manage');
Route::resource('permissions', PermissionController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('permission:roles.manage');
Route::resource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);
Route::redirect('/costumer', '/customers')->name('costumer.index');

Route::get('/kasir',[KasirController::class, 'index'])->name('kasir.index');
Route::post('/kasir', [KasirController::class, 'store'])->name('kasir.store');
Route::get('/kasir/list', [KasirController::class, 'list'])->name('kasir.list');
Route::get('/kasir/{id}/detail', [KasirController::class, 'getDetail']);
Route::get('/kasir/stok-realtime/{barang_id?}', [KasirController::class, 'getStokRealtime'])->name('kasir.stok-realtime');
Route::get('/kasir/barang-by-customer/{customer_id}', [KasirController::class, 'getBarangByCustomer'])->name('kasir.barang-by-customer');
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

Route::get('/sub-barang', [ControllerSub_barang::class, 'index'])->name('sub_barang.index');
Route::post('/sub-barang', [ControllerSub_barang::class, 'store'])->name('sub_barang.store');
Route::put('/sub-barang/{id}', [ControllerSub_barang::class, 'update'])->name('sub_barang.update');
Route::put('/sub-barang/delete/{id}', [ControllerSub_barang::class, 'destroy'])->name('sub_barang.destroy');



Route::get('/report',function(){
    return view('report');
});

Route::get('/stok', [ControllerStok::class, 'index'])->name('stok.index');

Route::get('/laba-rugi', [ControllerStok::class, 'getLabaRugi'])->name('stok.laba_rugi');
});
