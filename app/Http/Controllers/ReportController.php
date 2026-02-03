<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\ReportLayout;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

use function Symfony\Component\Clock\now;

class ReportController extends Controller
{
    public function menu()
    {
        return view('reports.menu');
    }

    public function index()
    {
        $user = auth()->user();
        $authLocIds = [];
        $locations = Location::all();
        $items = Item::all();

        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $authLocIds = $authLocs->pluck('id');
                $locations = $authLocs;
                $items = Item::whereIn('location_id', $authLocIds)->get();
            }
        }

        return view('reports.index', [
            'items' => $items,
            'categories' => Category::all(),
            'locations' => $locations,
        ]);
    }

    public function generate(Request $request)
    {
        return view('items.download_qr', [
            'title' => 'Laporan Inventaris',
            'message' => 'Laporan PDF sedang digenerate. Silakan tunggu sebentar.',
            'downloadUrl' => route('reports.inventory.download_file'),
            'redirectUrl' => route('reports.inventory'),
            'method' => 'POST',
            'params' => $request->all(),
        ]);
    }

    public function downloadFile(Request $request)
    {
        $query = Item::with(['location', 'category']);

        $user = auth()->user();
        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $query->whereIn('location_id', $authLocs->pluck('id'));
            }
        }
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

        $summary = [
            'kategori' => $items->pluck('category_id')->unique()->count(),
            'total' => $items->count(),
            'baik' => $items->where('condition', 'Baik')->count(),
            'rusak' => $items->where('condition', 'Rusak')->count(),
            'perbaikan' => $items->where('condition', 'Perbaikan')->count(),
        ];

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

        $setting = Setting::first();

        $pdf = PDF::loadView('reports.pdf', [
            'items' => $items,
            'columns' => $columns,
            'filters' => $filters,
            'summary' => $summary,
            'title' => $title,
            'printedAt' => $printedAt,
            'setting' => $setting,
        ]);

        $response = $pdf->download('laporan-inventaris.pdf');

        // Sinkronisasi: Set cookie agar client tahu file sudah dikirim
        if ($request->has('download_token')) {
            $response->withCookie(cookie('download_status', $request->download_token, 1, '/', null, false, false));
        }

        return $response;
    }
}
