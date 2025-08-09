@extends('layout')
@section('content-header')
    <h1>
        Pilih Laporan
    </h1>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h2>Kasir</h2>
                    <br /><br />
                </div>
                <a href="{{url('/kasir/list')}}" class="small-box-footer">Klik<i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h2>Pembelian</h2>
                    <br /><br />
                </div>
                <a href="{{url('/pembelian/list')}}" class="small-box-footer">Klik <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h2>Pemakaian </h2>
                    <h2>Barang</h2>
                </div>
                <a href="{{url('/pemakaian/list')}}" class="small-box-footer">Klik <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h2>Produksi</h2>
                    <br /><br />
                </div>
                <a href="{{url('/produksi/list')}}" class="small-box-footer">Klik <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h2>Laba Rugi</h2>
                    <br /><br />
                </div>
                <a href="/laba-rugi" class="small-box-footer">Klik <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h2>Stok</h2>
                    <br /><br />
                </div>
                <a href="{{url('/stok')}}" class="small-box-footer">Klik <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <!-- ./col -->
    </div>
@endsection
