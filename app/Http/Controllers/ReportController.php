<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\ReportLayout;
use Barryvdh\DomPDF\Facade\Pdf;

use function Symfony\Component\Clock\now;

class ReportController extends Controller
{
    public function menu()
    {
    return view('reports.menu');
    }

    public function index()
    {
        return view('reports.index', [
            'items' => Item::all(),
            'categories' => Category::all(),
            'locations' => Location::all(),
        ]);
         dd(config('report_fields.inventory'));
    }

   public function generate(Request $request)
    {
    $query = Item::with(['location', 'category']);
    if ($request->scope === 'barang' && $request->filled('item_id')) {
    $query->where('id', $request->item_id);
    }
    if ($request->filled('locations')) {
        $query->whereIn('location_id', $request->locations);
    }

    if ($request->filled('categories')) {
        $query->whereIn('category_id', $request->categories);
    }

    if ($request->filled('condition') && $request->condition !== 'all') {
    $query->whereRaw('LOWER(condition) = ?', [strtolower($request->condition)]);
    }

    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59',
        ]);
    }

    $items = $query->get();
$layout = ReportLayout::where('report_type', 'inventory')->first();


$columns = $layout && $layout->columns
? json_decode($layout->columns, true)
: [
    'uqcode',
    'name',
    'category.name',
    'location.name',
    'condition',
    'last_service_date',
    ];


    // 3️⃣ Ringkasan
    $summary = [
        'kategori' => $items->pluck('category_id')->unique()->count(),
        'total' => $items->count(),
        'baik' => $items->where('condition', 'Baik')->count(),
        'rusak' => $items->where('condition', 'Rusak')->count(),
        'perbaikan' => $items->where('condition', 'Perbaikan')->count(),
    ];

    // 4️⃣ FILTER INFO
    $filters = [
        'locations' => Location::whereIn('id', $request->locations ?? [])
            ->pluck('name')->toArray(),
        'categories' => Category::whereIn('id', $request->categories ?? [])
            ->pluck('name')->toArray(),
        'condition' => $request->condition ?? 'Semua',
        'periode' => ($request->from && $request->to)
            ? $request->from.' s/d '.$request->to
            : 'Semua',
    ];
    $title = 'Laporan Inventaris Barang';
    $printedAt = now()->format('d/m/Y H:i:s');

    // 5️⃣ BUAT PDF (INI YANG TADI HILANG)
    return PDF::loadView('reports.pdf', [
    'items' => $items,
    'columns' => $columns,
    'filters' => $filters,
    'summary' => $summary,
    'title' => $title,
    'printedAt' => $printedAt,
])->download('laporan-inventaris.pdf');


    // 6️⃣ RETURN
    return $pdf->download('laporan-inventaris.pdf');
}
}
