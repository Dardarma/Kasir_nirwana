<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $features = collect([
            'kasir.view' => ['Kasir', route('kasir.index')],
            'pembelian.view' => ['Pembelian', route('pembelian.index')],
            'pemakaian.view' => ['Pemakaian barang', route('pemakaian.index')],
            'produksi.view' => ['Produksi', url('/produksi')],
            'barang.view' => ['Barang', url('/barang')],
            'sub-barang.view' => ['Sub Barang', route('sub_barang.index')],
            'customers.view' => ['Pelanggan', route('customers.index')],
            'stock.view' => ['Stok', route('stok.index')],
            'reports.view' => ['Laporan lainnya', url('/report')],
            'laba-rugi.view' => ['Laba Rugi', route('stok.laba_rugi')],
            'users.view' => ['Manajemen User', route('users.index')],
            'roles.manage' => ['Role & Permission', route('roles.index')],
        ])->filter(fn ($feature, $permission) => $user->hasPermission($permission));

        if (! $user->hasPermission('dashboard.view')) {
            return view('dashboard.index', ['showReports' => false, 'features' => $features]);
        }

        $periods = ['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'];
        $input = $request->validate([
            'periode' => 'sometimes|required|in:harian,mingguan,bulanan',
            'tanggal' => 'nullable|date_format:Y-m-d',
        ]);

        $period = $input['periode'] ?? 'harian';
        $date = CarbonImmutable::parse($input['tanggal'] ?? 'today', 'Asia/Jakarta');
        [$start, $end] = match ($period) {
            'mingguan' => [$date->startOfWeek(CarbonImmutable::MONDAY), $date->endOfWeek(CarbonImmutable::SUNDAY)],
            'bulanan' => [$date->startOfMonth(), $date->endOfMonth()],
            default => [$date, $date],
        };

        $types = ['penjualan', 'produksi', 'pembelian'];
        $transactions = Transaksi::query()
            ->where('transaksi.status', 'aktif')
            ->whereBetween('transaksi.tanggal', [$start->toDateString(), $end->toDateString()]);

        $daily = (clone $transactions)
            ->whereIn('jenis_transaksi', $types)
            ->select('tanggal', 'jenis_transaksi')
            ->selectRaw('COUNT(*) AS jumlah, COALESCE(SUM(total_bayar), 0) AS nominal')
            ->groupBy('tanggal', 'jenis_transaksi')
            ->get()
            ->groupBy('tanggal');

        $totals = array_fill_keys($types, ['jumlah' => 0, 'nominal' => 0]);
        $days = [];

        foreach (CarbonPeriod::create($start->toDateString(), $end->toDateString()) as $day) {
            $values = array_fill_keys($types, ['jumlah' => 0, 'nominal' => 0]);
            foreach ($daily->get($day->toDateString(), []) as $row) {
                $type = $row->jenis_transaksi;
                $values[$type] = ['jumlah' => (int) $row->jumlah, 'nominal' => (int) $row->nominal];
                $totals[$type]['jumlah'] += $values[$type]['jumlah'];
                $totals[$type]['nominal'] += $values[$type]['nominal'];
            }
            $days[] = ['tanggal' => $day->locale('id')->isoFormat('ddd, D MMM'), 'values' => $values];
        }

        $products = (clone $transactions)
            ->where('jenis_transaksi', 'produksi')
            ->join('transaksi_detail', 'transaksi_detail.transaksi_id', '=', 'transaksi.id')
            ->where('transaksi_detail.status', 'aktif')
            ->select('transaksi_detail.nama_barang')
            ->selectRaw('transaksi_detail.satuan_stok AS satuan')
            ->selectRaw('SUM(transaksi_detail.jumlah_gram) AS jumlah')
            ->groupBy('transaksi_detail.nama_barang', 'transaksi_detail.satuan_stok')
            ->orderBy('transaksi_detail.nama_barang')
            ->orderBy('satuan')
            ->get();

        return view('dashboard.index', compact('periods', 'period', 'date', 'start', 'end', 'totals', 'days', 'products', 'features')
            + ['showReports' => true]);
    }
}
