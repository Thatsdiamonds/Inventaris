<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceReportController extends Controller
{
    public function generate(Request $request)
    {
        $query = Service::with('item');

        if ($request->filled('vendor')) {
            $query->where('vendor', $request->vendor);
        }

        if ($request->status === 'proses') {
            $query->whereNull('finished_at');
        }

        if ($request->status === 'selesai') {
            $query->whereNotNull('finished_at');
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('service_date', [
                $request->from,
                $request->to
            ]);
        }

        $services = $query->get();

        $summary = [
            'total'   => $services->count(),
            'proses'  => $services->whereNull('finished_at')->count(),
            'selesai' => $services->whereNotNull('finished_at')->count(),
        ];
$pdf = Pdf::loadView('reports.service_pdf', [
    'services'  => $services,
    'filters'   => [
        'vendor'  => $request->vendor ?? 'Semua',
        'status'  => $request->status ?? 'Semua',
        'periode' => ($request->from && $request->to)
            ? $request->from . ' s/d ' . $request->to
            : 'Semua',
    ],
    'summary'   => $summary,
    'title'     => 'Laporan Servis Barang',
    'printedAt' => now()->format('d/m/Y H:i:s'),
]);

        return $pdf->download('laporan-servis.pdf');
    }
}
