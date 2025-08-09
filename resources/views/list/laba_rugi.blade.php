@extends('layout')
@section('content-header')
    <h2>
        Laba Rugi
    </h2>
@endsection
@section('content')
    <div class="card mt-4">
        <div class="card-header">
            <div class="row align-items-center">

                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">

                        <form method="GET" action="{{ url('/laba-rugi') }}" class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                <select class="custom-select" name="paginate" onchange="this.form.submit()">
                                    <option value="10" {{ request('paginate') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('paginate') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('paginate') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('paginate') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>

                            <div class="input-group input-group-sm mx-1" style="width: 150px;">
                                <input type="month" class="form-control" id="monthPicker" name="month" 
                                       value="{{ request('month', date('Y-m')) }}" 
                                       onchange="this.form.submit()">
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>



        <div class="card-body">
            <div class="table-responsive">
                <div class="table-wrapper" style="overflow-x: auto; border-radius: 10px;">
                    <table id="data" class="table table-bordered table-hover"
                        style="border-radius: 10px; min-width: 900px;">
                        <thead style="background-color: #578FCA; color: white;">
                            <tr>
                                <th style="mix-width: 10px; white-space: nowrap;">No</th>
                                <th style="min-width: 150px; white-space: nowrap;">Tanggal</th>
                                <th style="min-width: 80px; white-space: nowrap;">Utang</th>
                                <th style="min-width: 80px; white-space: nowrap;">Piutang</th>
                                <th style="min-width: 80px; white-space: nowrap;">Pemasukan</th>
                                <th style="min-width: 100px; white-space: nowrap;">Pengeluaran</th>
                                <th style="min-width: 80px; white-space: nowrap;">Laba/Rugi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($data) == 0)
                                <tr>
                                    <td colspan="6" class="text-center">Data not found</td>
                                </tr>
                            @endif
                            @foreach ($data as $key => $item)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $key + 1 }}</td>
                                    <td style="white-space: nowrap;">{{ $item->tanggal}}</td>
                                    <td style="white-space: nowrap;">Rp {{number_format($item->hutang, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap;">Rp {{number_format($item->piutang, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap;">Rp {{number_format($item->pemasukan, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap;">Rp {{number_format($item->pengeluaran, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap;">Rp {{number_format($item->laba_rugi, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </tfoot>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Total</th>
                            <th>Rp {{ number_format($data->sum('hutang'), 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($data->sum('piutang'), 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($data->sum('pemasukan'), 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($data->sum('pengeluaran'), 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($data->sum('laba_rugi'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-auto m-2">
                {{-- <p>Showing {{ $stok->firstItem() }} to {{ $stok->lastItem() }} of {{ $stok->total() }} entries</p> --}}
            </div>
            <div class="col-auto m-2">
                {{-- {{ $stok->links() }} --}}
            </div>
        </div>
    </div>
    </div>
@endsection
