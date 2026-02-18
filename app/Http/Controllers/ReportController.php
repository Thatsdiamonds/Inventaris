<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\ReportLayout;
use App\Models\Setting;
use App\Models\Service;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Report Dashboard
     */
    public function menu()
    {
        $user = auth()->user();
        $authLocs = $user->isRoot() ? Location::all() : $user->authorizedLocations();
        
        return view('reports.menu', [
            'locations' => $authLocs,
            'categories' => Category::all(),
            'vendors' => Service::distinct()->pluck('vendor'),
        ]);
    }

    /**
     * Inventory Report Page
     */
    public function index()
    {
        $user = auth()->user();
        $locations = $user->isRoot() ? Location::all() : $user->authorizedLocations();
        
        return view('reports.index', [
            'items' => Item::all(), // For specific item selection
            'categories' => Category::all(),
            'locations' => $locations,
        ]);
    }

    /**
     * Global Generator View (Waiting Page)
     */
    public function generate(Request $request, $type = 'inventory')
    {
        $downloadRoute = $type === 'services' ? 'reports.services.download_file' : 'reports.inventory.download_file';
        
        return view('reports.waiting', [
            'title' => 'Laporan ' . ucfirst($type),
            'message' => 'Laporan sedang disiapkan. Silakan tunggu sebentar...',
            'downloadUrl' => route($downloadRoute),
            'redirectUrl' => route('reports.menu'),
            'method' => 'POST',
            'params' => $request->all()
        ]);
    }

    /**
     * Download Inventory PDF
     */
    public function downloadInventory(Request $request)
    {
        $items = $this->reportService->getInventoryData($request->all());
        $summary = $this->reportService->getInventorySummary($items);
        $setting = Setting::first();
        
        $layout = ReportLayout::where('report_type', 'inventory')->first();
        $columns = $layout && $layout->columns ? (is_array($layout->columns) ? $layout->columns : json_decode($layout->columns, true)) : ['uqcode', 'name', 'category.name', 'location.name', 'condition'];

        $filters = [
            'locations' => Location::whereIn('id', (array)($request->locations ?? []))->pluck('name')->toArray(),
            'categories' => Category::whereIn('id', (array)($request->categories ?? []))->pluck('name')->toArray(),
            'condition' => $request->condition ?? 'Semua',
            'periode' => ($request->from && $request->to) ? $request->from . ' s/d ' . $request->to : 'Semua',
        ];

        $pdf = PDF::loadView('reports.pdf', [
            'items' => $items,
            'columns' => $columns,
            'filters' => $filters,
            'summary' => $summary,
            'title' => 'Laporan Inventaris Barang',
            'printedAt' => now()->format('d/m/Y H:i:s'),
            'setting' => $setting,
        ]);

        return $pdf->download('laporan-inventaris-' . date('Ymd') . '.pdf');
    }

    /**
     * Download Service PDF
     */
    public function downloadServices(Request $request)
    {
        $services = $this->reportService->getServiceData($request->all());
        $summary = $this->reportService->getServiceSummary($services);
        $setting = Setting::first();

        $pdf = Pdf::loadView('reports.service_pdf', [
            'services' => $services,
            'filters' => [
                'vendor' => $request->vendor ?? 'Semua',
                'status' => $request->status ?? 'Semua',
                'periode' => ($request->from && $request->to) ? $request->from . ' s/d ' . $request->to : 'Semua',
            ],
            'summary' => $summary,
            'title' => 'Laporan Servis Barang',
            'printedAt' => now()->format('d/m/Y H:i:s'),
            'setting' => $setting,
        ]);

        return $pdf->download('laporan-servis-' . date('Ymd') . '.pdf');
    }

    /**
     * Live Preview AJAX (Bonus factor)
     */
    public function preview(Request $request)
    {
        $type = $request->type ?? 'inventory';
        
        if ($type === 'inventory') {
            $data = $this->reportService->getInventoryData($request->all())->take(10);
            return response()->json([
                'html' => view('reports.partials.inventory_preview', ['items' => $data])->render(),
                'count' => $data->count()
            ]);
        } else {
            $data = $this->reportService->getServiceData($request->all())->take(10);
            return response()->json([
                'html' => view('reports.partials.service_preview', ['services' => $data])->render(),
                'count' => $data->count()
            ]);
        }
    }
}
