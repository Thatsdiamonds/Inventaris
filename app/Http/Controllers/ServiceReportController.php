<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceReportController extends Controller
{
    public function generate(Request $request)
    {
        return view('items.download_qr', [
            'title' => 'Laporan Servis',
            'message' => 'Laporan PDF servis sedang digenerate.',
            'downloadUrl' => route('reports.services.download_file'),
            'redirectUrl' => route('reports.menu'),
            'method' => 'POST',
            'params' => $request->all()
        ]);
    }

    public function downloadFile(Request $request)
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
        $setting = Setting::first();
        
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
            'setting'   => $setting,
        ]);

        $response = $pdf->download('laporan-servis.pdf');

        // Sinkronisasi: Set cookie agar client tahu file sudah dikirim
        if ($request->has('download_token')) {
            $response->withCookie(cookie('download_status', $request->download_token, 1, '/', null, false, false));
        }

        return $response;
    }
}
