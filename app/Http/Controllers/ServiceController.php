<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Service;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'in_service');
        $search = $request->input('search');
        $locationId = $request->input('location_id');
        $categoryId = $request->input('category_id');

        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $applyServiceFilters = function ($query) use ($search, $locationId, $categoryId) {
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('item', function ($iq) use ($search) {
                        $iq->where('name', 'LIKE', "%$search%")
                            ->orWhere('uqcode', 'LIKE', "%$search%");
                    })
                        ->orWhere('vendor', 'LIKE', "%$search%")
                        ->orWhere('description', 'LIKE', "%$search%");
                });
            }
            if ($locationId) {
                $query->whereHas('item', fn($q) => $q->where('location_id', $locationId));
            }
            if ($categoryId) {
                $query->whereHas('item', fn($q) => $q->where('category_id', $categoryId));
            }
        };

        $applyItemFilters = function ($query) use ($search, $locationId, $categoryId) {
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhere('uqcode', 'LIKE', "%$search%");
                });
            }
            if ($locationId) {
                $query->where('location_id', $locationId);
            }
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
        };

        $today = date('Y-m-d');
        $thirtyDaysLater = date('Y-m-d', strtotime('+30 days'));

        $inServiceQuery = Service::with(['item.category', 'item.location'])->whereNull('date_out');
        $applyServiceFilters($inServiceQuery);
        $inService = $inServiceQuery->orderBy('date_in', 'desc')->get();

        $needsServiceQuery = Item::with(['category', 'location'])
            ->where('is_active', true)
            ->where('service_required', true)
            ->whereNotNull('service_interval_days')
            ->where(function ($q) use ($today) {
                $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";
                $q->whereRaw("$rawNextDate <= ?", [$today]);
            })
            ->whereDoesntHave('services', function ($q) {
                $q->whereNull('date_out');
            });
        $applyItemFilters($needsServiceQuery);
        $needsService = $needsServiceQuery->get();

        $upcomingQuery = Item::with(['category', 'location'])
            ->where('is_active', true)
            ->where('service_required', true)
            ->whereNotNull('service_interval_days')
            ->whereDoesntHave('services', function ($q) {
                $q->whereNull('date_out');
            });
        
        $upcomingFilter = $request->input('upcoming_filter', '30_days');
        
        $upcomingQuery->where(function ($q) use ($today, $thirtyDaysLater, $upcomingFilter) {
            $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";
            
            $q->whereRaw("$rawNextDate > ?", [$today]);
            
            if ($upcomingFilter == '30_days') {
                $q->whereRaw("$rawNextDate <= ?", [$thirtyDaysLater]);
            }
        });
        
        $applyItemFilters($upcomingQuery);
        $upcoming = $upcomingQuery->get();

        $completedQuery = Service::with(['item.category', 'item.location'])->whereNotNull('date_out');
        $applyServiceFilters($completedQuery);
        $completed = $completedQuery->orderBy('date_out', 'desc')->paginate(10, ['*'], 'completed_page')->withQueryString();

        $allItemsQuery = Item::with(['category', 'location'])
            ->where('is_active', true)
            ->where('service_required', true)
            ->where(function($q) use ($today) {
                $q->whereHas('services', function($sq) { $sq->whereNull('date_out'); })
                ->orWhereNotNull('service_interval_days');
            });
        $applyItemFilters($allItemsQuery);
        $allItems = $allItemsQuery->orderBy('name')->paginate(20, ['*'], 'all_page')->withQueryString();

        $counts = [
            'in_service' => Service::whereNull('date_out')->count(),
            'needs_service' => Item::where('is_active', true)
                ->where('service_required', true)
                ->whereNotNull('service_interval_days')
                ->where(function ($q) use ($today) {
                    $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";
                    $q->whereRaw("$rawNextDate <= ?", [$today]);
                })
                ->whereDoesntHave('services', function ($q) {
                    $q->whereNull('date_out');
                })->count(),
            'upcoming' => Item::where('is_active', true)
                ->where('service_required', true)
                ->whereNotNull('service_interval_days')
                ->where(function ($q) use ($today, $thirtyDaysLater) {
                    $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";
                    $q->whereRaw("$rawNextDate > ?", [$today])
                        ->whereRaw("$rawNextDate <= ?", [$thirtyDaysLater]);
                })
                ->whereDoesntHave('services', function ($q) {
                    $q->whereNull('date_out');
                })->count(),
        ];

        return view('services.index', compact(
            'inService', 'needsService', 'upcoming', 'completed', 'allItems',
            'tab', 'counts', 'categories', 'locations'
        ));
    }

    public function create(Item $item)
    {
        return view('services.create', compact('item'));
    }

    public function confirm(Request $request, Item $item)
    {
        $validated = $request->validate([
            'vendor' => 'required|string',
            'date_in' => 'required|date',
            'description' => 'required|string',
        ]);

        $serviceType = $request->input('service_type', 'routine');
        return view('services.confirm', array_merge($validated, ['item' => $item, 'service_type' => $serviceType]));
    }

    public function store(Request $request, Item $item)
    {
        $validated = $request->validate([
            'vendor' => 'required|string',
            'date_in' => 'required|date',
            'description' => 'required|string',
            'service_type' => 'required|in:routine,manual',
        ]);

        Service::create([
            'item_id' => $item->id,
            'vendor' => $validated['vendor'],
            'date_in' => $validated['date_in'],
            'description' => $validated['description'] . ($validated['service_type'] == 'manual' ? ' [Manual]' : ' [Routine]'),
        ]);

        $updateData = ['condition' => 'perbaikan'];
        
        if ($validated['service_type'] == 'routine') {
            $updateData['last_service_date'] = $validated['date_in'];
        }

        $item->update($updateData);

        return redirect()->route('services.index')->with('success', 'Pencatatan servis dimulai. Barang dialihkan ke status "Perbaikan".');
    }

    public function finish(Request $request, Service $service)
    {
        $request->validate([
            'date_out' => 'required|date|after_or_equal:date_in',
            'cost' => 'nullable|numeric',
            'condition_after' => 'required|in:baik,rusak,perbaikan',
        ]);

        $service->update([
            'date_out' => $request->date_out,
            'cost' => $request->cost,
        ]);

        $service->item->update([
            'condition' => $request->condition_after,
            'last_service_date' => $request->date_out,
        ]);

        return redirect()->route('services.index')->with('success', 'Servis selesai. Status barang diperbarui.');
    }

    public function fails(Request $request, Service $service)
    {
        $request->validate([
            'date_out' => 'required|date|after_or_equal:date_in',
        ]);

        $service->update([
            'date_out' => $request->date_out,
            'description' => $service->description.' (Gagal Servis)',
        ]);

        $service->item->update([
            'condition' => 'rusak',
            'last_service_date' => $request->date_out,
        ]);

        return redirect()->route('services.index')->with('success', 'Servis ditandai Gagal. Kondisi barang kini "Rusak".');
    }
}
