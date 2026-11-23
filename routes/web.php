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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportPdfController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::resource('satuan', \App\Http\Controllers\SatuanController::class)
    ->parameters(['satuan' => 'satuan'])
    ->only(['index', 'store', 'update', 'destroy'])
    ->middlewareFor('index', 'permission:barang.view')
    ->middlewareFor('store', 'permission:barang.create')
    ->middlewareFor('update', 'permission:barang.update')
    ->middlewareFor('destroy', 'permission:barang.delete');

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
Route::resource('customers', CustomerController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middlewareFor('index', 'permission:customers.view')
    ->middlewareFor('store', 'permission:customers.create')
    ->middlewareFor('update', 'permission:customers.update')
    ->middlewareFor('destroy', 'permission:customers.delete');
Route::redirect('/costumer', '/customers')->middleware('permission:customers.view')->name('costumer.index');

Route::get('/kasir',[KasirController::class, 'index'])->middleware('permission:kasir.view')->name('kasir.index');
Route::post('/kasir', [KasirController::class, 'store'])->middleware('permission:kasir.create')->name('kasir.store');
Route::get('/kasir/list', [KasirController::class, 'list'])->middleware('permission:kasir.view')->name('kasir.list');
Route::get('/kasir/{id}/nota', [KasirController::class, 'downloadNota'])->middleware('permission:kasir.view')->name('kasir.nota');
Route::get('/kasir/{id}/detail', [KasirController::class, 'getDetail'])->middleware('permission:kasir.view');
Route::get('/kasir/stok-realtime/{barang_id?}', [KasirController::class, 'getStokRealtime'])->middleware('permission:kasir.create')->name('kasir.stok-realtime');
Route::get('/kasir/barang-by-customer/{customer_id}', [KasirController::class, 'getBarangByCustomer'])->middleware('permission:kasir.create')->name('kasir.barang-by-customer');
Route::put('/kasir/edit/{id}', [KasirController::class, 'editStatusPembayaran'])->middleware('permission:kasir.update')->name('kasir.update');
Route::put('/kasir/delete/{id}', [KasirController::class, 'destroy'])->middleware('permission:kasir.delete')->name('kasir.destroy');


Route::get('/barang', [Barang_controller::class, 'index'])->middleware('permission:barang.view');
Route::post('/barang', [Barang_controller::class, 'store'])->middleware('permission:barang.create')->name('Barang_store');
Route::put('/barang/{id}', [Barang_controller::class, 'update'])->middleware('permission:barang.update')->name('Barang_update');
Route::put('/barang/delete/{id}', [Barang_controller::class, 'destroy'])->middleware('permission:barang.delete')->name('Barang_destroy');

Route::get('/pembelian', [PembelianController::class, 'index'])->middleware('permission:pembelian.view')->name('pembelian.index');
Route::post('/pembelian', [PembelianController::class, 'store'])->middleware('permission:pembelian.create')->name('pembelian.store');
Route::get('/pembelian/list', [PembelianController::class, 'list'])->middleware('permission:pembelian.view')->name('pembelian.list');
Route::get('/pembelian/{id}/detail', [PembelianController::class, 'getDetail'])->middleware('permission:pembelian.view');
Route::put('/pembelian/edit/{id}', [PembelianController::class, 'editStatusPembayaran'])->middleware('permission:pembelian.update')->name('pembelian.update');
Route::put('/pembelian/delete/{id}', [PembelianController::class, 'destroy'])->middleware('permission:pembelian.delete')->name('pembelian.destroy');

Route::get('/produksi', [ProduksiController::class, 'index'])->middleware('permission:produksi.view');
Route::post('/produksi', [ProduksiController::class, 'store'])->middleware('permission:produksi.create')->name('produksi.store');
Route::get('/produksi/list', [ProduksiController::class, 'list'])->middleware('permission:produksi.view')->name('produksi.list');
Route::get('/produksi/{id}/detail', [ProduksiController::class, 'getDetail'])->middleware('permission:produksi.view');
Route::put('/produksi/delete/{id}', [ProduksiController::class, 'destroy'])->middleware('permission:produksi.delete')->name('produksi.destroy');


Route::get('/pemakaian', [ControllerPemakaian::class, 'index'])->middleware('permission:pemakaian.view')->name('pemakaian.index');
Route::post('/pemakaian', [ControllerPemakaian::class, 'store'])->middleware('permission:pemakaian.create')->name('pemakaian.store');
Route::get('/pemakaian/list', [ControllerPemakaian::class, 'list'])->middleware('permission:pemakaian.view')->name('pemakaian.list');
Route::get('/pemakaian/{id}/detail', [ControllerPemakaian::class, 'getDetail'])->middleware('permission:pemakaian.view');
Route::put('/pemakaian/delete/{id}', [ControllerPemakaian::class, 'destroy'])->middleware('permission:pemakaian.delete')->name('pemakaian.destroy');
Route::get('/pemakaian/stok-realtime/{barang_id?}', [ControllerPemakaian::class, 'getStokRealtime'])->middleware('permission:pemakaian.create')->name('pemakaian.stok-realtime');

Route::get('/sub-barang', [ControllerSub_barang::class, 'index'])->middleware('permission:sub-barang.view')->name('sub_barang.index');
Route::post('/sub-barang', [ControllerSub_barang::class, 'store'])->middleware('permission:sub-barang.create')->name('sub_barang.store');
Route::put('/sub-barang/{id}', [ControllerSub_barang::class, 'update'])->middleware('permission:sub-barang.update')->name('sub_barang.update');
Route::put('/sub-barang/delete/{id}', [ControllerSub_barang::class, 'destroy'])->middleware('permission:sub-barang.delete')->name('sub_barang.destroy');



Route::get('/report/pdf', [ReportPdfController::class, 'download'])->name('reports.pdf');

Route::get('/stok', [ControllerStok::class, 'index'])->middleware('permission:stock.view')->name('stok.index');

Route::get('/laba-rugi', [ControllerStok::class, 'getLabaRugi'])->middleware('permission:laba-rugi.view')->name('stok.laba_rugi');
});
