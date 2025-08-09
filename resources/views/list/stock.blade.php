@extends('layout')
@section('content-header')
<h2>
    STOK
</h2>
@endsection
@section('content')
 <div class="card mt-4">
                <div class="card-header">
                    <div class="row align-items-center">

                        <div class="col-12 col-md">
                            <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                                
                                <form method="GET" action="{{ url('/stok') }}" class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                        <select class="custom-select" name="paginate" onchange="this.form.submit()">
                                            <option value="10" {{ request('paginate') == 10 ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ request('paginate') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('paginate') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('paginate') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>

                                      <div class="input-group input-group-sm mx-1" style="width: 100px;">
                                        <select class="custom-select" name="filter" onchange="this.form.submit()">
                                            <option value="semua" {{ request('filter') == 'semua' || request('filter') == '' ? 'selected' : '' }}>Semua</option>
                                            <option value="jadi" {{ request('filter') == 'jadi' ? 'selected' : '' }}>Jadi</option>
                                            <option value="produksi" {{ request('filter') == 'produksi' ? 'selected' : '' }}>Produksi</option>
                                        </select>
                                    </div>

                                    <div class="input-group input-group-sm mx-1" style="width: 150px;">
                                        <input type="text" name="table_search" class="form-control" placeholder="Search" value="{{ request('table_search') }}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                    
                            </div>
                        </div>
                    </div>
                </div>
                
                

                <div class="card-body">  
                    <div class="table-responsive">
                        <div class="table-wrapper" style="overflow-x: auto; border-radius: 10px;">
                            <table id="data" class="table table-bordered table-hover" style="border-radius: 10px; min-width: 900px;">
                              <thead style="background-color: #578FCA; color: white;">
                                <tr>
                                    <th style="min-width: 60px; white-space: nowrap;">No</th>
                                    <th style="min-width: 150px; white-space: nowrap;">Nama Barang</th>
                                    <th style="min-width: 80px; white-space: nowrap;">Kategori</th>
                                    <th style="min-width: 100px; white-space: nowrap;">Sub Kategori</th>
                                    <th style="min-width: 80px; white-space: nowrap;">Harga</th>
                                    <th style="min-width: 80px; white-space: nowrap;">Stok</th>
                                </tr>
                            </thead>
                        <tbody>
                            @if(count($stok) == 0)
                                <tr>
                                    <td colspan="6" class="text-center">Data not found</td>
                                </tr>
                            @endif
                            @foreach ($stok as $key => $item)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $key + 1 }}</td>
                                    <td style="white-space: nowrap;">{{ $item->nama_barang }}</td>
                                    <td style="white-space: nowrap;">{{ ucfirst($item->kategori) }}</td>
                                    <td style="white-space: nowrap;">{{ str_replace('_', ' ', ucfirst($item->sub_kategori)) }}</td>
                                    <td style="white-space: nowrap;">{{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap; max-width: 300px; overflow: hidden; text-overflow: ellipsis;" >{{ $item->stok }}</td>                                  
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    </div>
                </div>
                <div class="row justify-content-end">
                    <div class="col-auto m-2">
                        <p>Showing {{ $stok->firstItem() }} to {{ $stok->lastItem() }} of {{ $stok->total() }} entries</p>
                    </div>
                    <div class="col-auto m-2">
                        {{$stok->links()}}
                    </div>
                </div>
                </div>
            </div>

@endsection