<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\ReportLayout;
use App\Models\Service;
use App\Models\Setting;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
    public function exportInventoryExcel(Request $request)
{
    $query = \App\Models\Item::with(['category','location']);

    if ($request->filled('location_id')) {
        $query->where('location_id', $request->location_id);
    }

    if ($request->filled('condition')) {
        $query->where('condition', $request->condition);
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('acquisition_date', [
            $request->start_date,
            $request->end_date
        ]);
    }

    $items = $query->get();

    // ambil layout dari database
    $layout = \App\Models\ReportLayout::where('report_type', 'inventory')->first();

    $columns = $layout && $layout->columns
        ? (is_array($layout->columns)
            ? $layout->columns
            : json_decode($layout->columns, true))
        : ['uqcode','name','category.name','location.name','condition','acquisition_date'];

    $data = $query->get()->map(function ($item) use ($columns) {

    return collect($columns)->mapWithKeys(function ($col) use ($item) {

        return [
            $col => data_get($item, $col) ?? '-'
        ];

    });
});

    return \Maatwebsite\Excel\Facades\Excel::download(
    new class($data, $columns) implements
        \Maatwebsite\Excel\Concerns\FromCollection,
        \Maatwebsite\Excel\Concerns\WithHeadings,
        \Maatwebsite\Excel\Concerns\ShouldAutoSize,
        \Maatwebsite\Excel\Concerns\WithStyles {

        private $data;
        private $columns;

        public function __construct($data, $columns) {
            $this->data = $data;
            $this->columns = $columns;
        }

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return collect($this->columns)->map(function ($col) {
                return match($col) {
                    'uqcode' => 'Kode Unik',
                    'name' => 'Nama Barang',
                    'category.name' => 'Kategori',
                    'location.name' => 'Lokasi',
                    'condition' => 'Kondisi',
                    'acquisition_date' => 'Tanggal Perolehan',
                    'created_at' => 'Tanggal Inventarisasi',
                    'service_interval_days' => 'Maintenance (Hari)',
                    'last_service_date' => 'Last Service Date',
                    default => ucfirst($col),
                };
            })->toArray();
        }

        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
        {
            $rowCount = $sheet->getHighestRow();
            $colCount = count($this->columns);
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

            // HEADER STYLE
            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2F5597'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            $sheet->getRowDimension(1)->setRowHeight(28);

            // DATA STYLE
            $sheet->getStyle("A2:{$lastColumn}{$rowCount}")->applyFromArray([
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            for ($i = 2; $i <= $rowCount; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(22);
            }

            return [];
        }
    },
    'laporan-inventory.xlsx'
);
}
public function exportServiceExcel(Request $request)
{
    $query = \App\Models\Service::with(['item.location']);

    if ($request->filled('location_id')) {
        $query->whereHas('item', function($q) use ($request) {
            $q->where('location_id', $request->location_id);
        });
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('date_in', [
            $request->start_date,
            $request->end_date
        ]);
    }

    $data = $query->get()->map(function ($service) {
        return [
            'Nama Barang' => $service->item->name ?? '-',
            'Lokasi' => $service->item->location->name ?? '-',
            'Tanggal Masuk' => $service->date_in,
            'Tanggal Keluar' => $service->date_out,
            'Status' => $service->date_out ? 'Selesai' : 'Proses',
        ];
    });

    return Excel::download(
    new class($data) implements
        FromCollection,
        WithHeadings,
        ShouldAutoSize,
        WithStyles {

        private $data;

        public function __construct($data) {
            $this->data = $data;
        }

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return [
                'Nama Barang',
                'Lokasi',
                'Tanggal Masuk',
                'Tanggal Keluar',
                'Status'
            ];
        }

        public function styles(Worksheet $sheet)
        {
            $rowCount = $sheet->getHighestRow();
            $colCount = 5;
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

            // HEADER STYLE (SAMA PERSIS INVENTORY)
            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2F5597'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            $sheet->getRowDimension(1)->setRowHeight(28);

            // DATA STYLE
            $sheet->getStyle("A2:{$lastColumn}{$rowCount}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            for ($i = 2; $i <= $rowCount; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(22);
            }

            return [];
        }

    },
    'laporan-service.xlsx'
);
}
    public function menu(Request $request)
    {
        // If POST request with a scope, treat it as a generation request
        if ($request->isMethod('post') && $request->has('scope')) {
            $type = $request->input('type', 'inventory');
            return $this->generate($request, $type);
        }

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
    $downloadRoute = $type === 'services'
        ? 'reports.services.export_excel'
        : 'reports.inventory.export_excel';

    return view('reports.waiting', [
        'title' => 'Laporan ' . ucfirst($type),
        'message' => 'Laporan sedang disiapkan. Silakan tunggu sebentar...',
        'downloadUrl' => route($downloadRoute, $request->all()),
        'redirectUrl' => route('reports.menu'),
        'method' => 'GET',
        'params' => [],
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
            'locations' => Location::whereIn('id', (array) ($request->locations ?? []))->pluck('name')->toArray(),
            'categories' => Category::whereIn('id', (array) ($request->categories ?? []))->pluck('name')->toArray(),
            'condition' => $request->condition ?? 'Semua',
            'periode' => ($request->from && $request->to) ? $request->from.' s/d '.$request->to : 'Semua',
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

        return $pdf->download('laporan-inventaris-'.date('Ymd').'.pdf');
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
                'periode' => ($request->from && $request->to) ? $request->from.' s/d '.$request->to : 'Semua',
            ],
            'summary' => $summary,
            'title' => 'Laporan Servis Barang',
            'printedAt' => now()->format('d/m/Y H:i:s'),
            'setting' => $setting,
        ]);

        return $pdf->download('laporan-servis-'.date('Ymd').'.pdf');
    }

    /**
     * Live Preview AJAX (Bonus factor)
     */
    public function preview(Request $request)
    {
        $type = $request->type ?? 'inventory';

        if ($type === 'inventory') {
            $data = $this->reportService->getInventoryData($request->all(), 10);

            return response()->json([
                'html' => view('reports.partials.inventory_preview', ['items' => $data])->render(),
                'count' => $data->count(),
            ]);
        } else {
            $data = $this->reportService->getServiceData($request->all(), 10);

            return response()->json([
                'html' => view('reports.partials.service_preview', ['services' => $data])->render(),
                'count' => $data->count(),
            ]);
        }
    }
}
