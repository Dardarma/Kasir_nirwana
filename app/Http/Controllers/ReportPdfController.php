<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportPdfController extends Controller
{
    public function download(Request $request)
    {
        $filters = $request->validate([
            'jenis' => ['required', Rule::in(['kasir', 'pembelian', 'pemakaian', 'produksi', 'laba-rugi', 'stok'])],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'kategori' => ['nullable', Rule::in(['semua', 'jadi', 'produksi'])],
            'sumber' => ['nullable', Rule::in(['transaksi'])],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
            'barang_id' => ['nullable', 'integer', 'exists:barang,id'],
            'sub_barang_id' => ['nullable', 'integer', 'exists:sub_barang,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $jenis = $filters['jenis'];
        $permission = match ($jenis) {
            'kasir' => 'kasir.view',
            'pembelian' => 'pembelian.view',
            'pemakaian' => 'pemakaian.view',
            'produksi' => 'produksi.view',
            'laba-rugi' => 'laba-rugi.view',
            'stok' => 'stock.view',
        };
        abort_unless($request->user()->hasPermission($permission), 403);

        $bulan = (int) ($filters['bulan'] ?? now()->month);
        $tahun = (int) ($filters['tahun'] ?? now()->year);
        $judul = strtoupper(str_replace('-', ' ', $jenis));
        $dariModal = ($filters['sumber'] ?? null) === 'transaksi' && in_array($jenis, ['kasir', 'pembelian', 'pemakaian', 'produksi'], true);
        $periode = $jenis === 'stok' ? 'Stok saat ini' : ($dariModal
            ? 'Tanggal: ' . ($filters['tanggal_dari'] ?? 'Awal') . ' s/d ' . ($filters['tanggal_sampai'] ?? 'Akhir')
            : sprintf('Periode %02d/%04d', $bulan, $tahun));
        $lines = [];

        if ($jenis === 'stok') {
            $kategori = $filters['kategori'] ?? 'semua';
            $periode .= ' | Kategori: ' . ucfirst($kategori);
            $barang = Barang::query()->where('status', 'aktif')
                ->whereNotIn('sub_kategori', ['penolong/alat', Barang::NON_STOK])
                ->when($kategori !== 'semua', fn ($q) => $q->where('kategori', $kategori))
                ->orderBy('nama_barang')->get();
            $lines[] = sprintf('%-4s %-42s %-12s %16s', 'No', 'Barang', 'Kategori', 'Stok');
            foreach ($barang as $index => $item) {
                $masuk = DB::table('stok_masuk')->where('barang_id', $item->id)->sum('qty_masuk');
                $keluar = DB::table('stok_keluar')->where('barang_id', $item->id)->sum('qty_keluar');
                $lines[] = sprintf('%-4d %-42s %-12s %16s', $index + 1, mb_strimwidth($item->nama_barang, 0, 42), $item->kategori, format_jumlah($masuk - $keluar) . ' ' . $item->satuan);
            }
        } elseif ($jenis === 'laba-rugi') {
            $rows = DB::table('transaksi')->selectRaw('DATE(tanggal) as hari')
                ->selectRaw('SUM(CASE WHEN jenis_transaksi = "penjualan" THEN total_bayar ELSE 0 END) as pemasukan')
                ->selectRaw('SUM(CASE WHEN jenis_transaksi = "pembelian" THEN total_bayar ELSE 0 END) as pengeluaran')
                ->where('status', 'aktif')->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
                ->groupByRaw('DATE(tanggal)')->orderBy('hari')->get();
            $lines[] = sprintf('%-14s %20s %20s %20s', 'Tanggal', 'Pemasukan', 'Pengeluaran', 'Laba/Rugi');
            foreach ($rows as $row) {
                $lines[] = sprintf('%-14s %20s %20s %20s', $row->hari, format_rupiah($row->pemasukan), format_rupiah($row->pengeluaran), format_rupiah($row->pemasukan - $row->pengeluaran));
            }
            $lines[] = str_repeat('-', 82);
            $lines[] = sprintf('%-14s %20s %20s %20s', 'TOTAL', format_rupiah($rows->sum('pemasukan')), format_rupiah($rows->sum('pengeluaran')), format_rupiah($rows->sum('pemasukan') - $rows->sum('pengeluaran')));
        } else {
            $tipe = ['kasir' => 'penjualan', 'pembelian' => 'pembelian', 'pemakaian' => 'pemakaian', 'produksi' => 'produksi'][$jenis];
            $barangId = $dariModal && $jenis !== 'kasir' ? ($filters['barang_id'] ?? null) : null;
            $subBarangId = $dariModal && $jenis === 'kasir' ? ($filters['sub_barang_id'] ?? null) : null;
            $customerId = $dariModal && $jenis === 'kasir' ? ($filters['customer_id'] ?? null) : null;
            if ($barangId) {
                $periode .= ' | Barang: ' . Barang::findOrFail($barangId)->nama_barang;
            }
            if ($subBarangId) {
                $periode .= ' | Barang: ' . \App\Models\sub_barang::findOrFail($subBarangId)->sub_nama;
            }
            if ($customerId) {
                $periode .= ' | Pelanggan: ' . \App\Models\Customer::findOrFail($customerId)->name;
            }
            $rows = Transaksi::query()->with(['transaksiDetail', 'customerData'])->where('jenis_transaksi', $tipe)->where('status', 'aktif')
                ->when($dariModal, function ($query) use ($filters, $barangId, $subBarangId, $customerId) {
                    $query->when($filters['tanggal_dari'] ?? null, fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
                        ->when($filters['tanggal_sampai'] ?? null, fn ($q, $date) => $q->whereDate('tanggal', '<=', $date))
                        ->when($barangId, fn ($q) => $q->whereHas('transaksiDetail', fn ($detail) => $detail->where('barang_id', $barangId)))
                        ->when($subBarangId, fn ($q) => $q->whereHas('transaksiDetail', fn ($detail) => $detail->where('sub_barang_id', $subBarangId)))
                        ->when($customerId, fn ($q) => $q->where('customer_id', $customerId));
                }, fn ($query) => $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan))
                ->orderBy('tanggal')->orderBy('id')->get();
            $monetary = in_array($jenis, ['kasir', 'pembelian'], true);
            $lines[] = match ($jenis) {
                'kasir' => sprintf('%-4s %-28s %-20s %-12s %15s %-12s', 'No', 'Kode Transaksi', 'Pelanggan', 'Tanggal', 'Total', 'Status'),
                'pembelian' => sprintf('%-4s %-30s %-14s %18s %-12s', 'No', 'Kode Transaksi', 'Tanggal', 'Total', 'Status'),
                default => sprintf('%-4s %-35s %-14s', 'No', 'Kode Transaksi', 'Tanggal'),
            };
            if ($rows->isEmpty()) {
                $lines[] = 'Tidak ada transaksi sesuai filter.';
            }
            foreach ($rows as $index => $row) {
                $lines[] = match ($jenis) {
                    'kasir' => sprintf('%-4d %-28s %-20s %-12s %15s %-12s', $index + 1, mb_strimwidth($row->kode_transaksi, 0, 28), mb_strimwidth($row->customerData?->name ?? $row->customer ?? '-', 0, 20), $row->tanggal, format_rupiah($row->total_bayar), $row->status_pembayaran),
                    'pembelian' => sprintf('%-4d %-30s %-14s %18s %-12s', $index + 1, mb_strimwidth($row->kode_transaksi, 0, 30), $row->tanggal, format_rupiah($row->total_bayar), $row->status_pembayaran),
                    default => sprintf('%-4d %-35s %-14s', $index + 1, mb_strimwidth($row->kode_transaksi, 0, 35), $row->tanggal),
                };
                foreach ($row->transaksiDetail as $detail) {
                    $lines[] = '     - ' . mb_strimwidth($detail->nama_barang ?? '-', 0, 55) . ' (' . format_jumlah($detail->jumlah) . ' ' . $detail->satuan . ')';
                }
            }
            $lines[] = str_repeat('-', 90);
            $lines[] = $monetary
                ? sprintf('%-67s %17s', 'TOTAL TRANSAKSI', format_rupiah($rows->sum('total_bayar')))
                : 'TOTAL TRANSAKSI: ' . $rows->count();
        }

        if (count($lines) === 1) {
            $lines[] = 'Tidak ada data pada filter ini.';
        }

        $pdf = $this->makePdf($judul, $periode, $lines);
        $namaFile = $dariModal ? "laporan-{$jenis}-transaksi.pdf" : "laporan-{$jenis}-{$tahun}-{$bulan}.pdf";
        return response()->streamDownload(fn () => print($pdf), $namaFile, ['Content-Type' => 'application/pdf']);
    }

    private function makePdf(string $title, string $period, array $lines): string
    {
        $chunks = array_chunk($lines, 40);
        $pages = [];
        foreach ($chunks as $pageIndex => $chunk) {
            if ($pageIndex > 0) {
                array_unshift($chunk, $lines[0]);
            }
            $content = '';
            $write = function (int $x, int $y, string $value, int $size = 9) use (&$content): void {
                $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
                $value = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
                $content .= "BT /F1 {$size} Tf 1 0 0 1 {$x} {$y} Tm ({$value}) Tj ET\n";
            };
            $write(35, 550, 'NIRWANA | LAPORAN ' . $title, 15);
            $write(35, 528, $period . ' | Dicetak: ' . now()->format('d-m-Y H:i'));
            $content .= "0.6 w 35 516 m 807 516 l S\n";
            foreach ($chunk as $index => $line) {
                $write(35, 495 - $index * 10, $line, 8);
            }
            $write(750, 25, 'Hal. ' . ($pageIndex + 1));
            $pages[] = $content;
        }

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>',
        ];
        $kids = [];
        foreach ($pages as $content) {
            $pageId = count($objects) + 1;
            $kids[] = "{$pageId} 0 R";
            $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents " . ($pageId + 1) . ' 0 R >>';
            $objects[] = '<< /Length ' . strlen($content) . ">>\nstream\n{$content}\nendstream";
        }
        $objects[1] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
        }
        return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
