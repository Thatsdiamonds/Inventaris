<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\ItemType;
use App\Models\Location;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        // 1. Optimize data retrieval for filters (Cache for 60 minutes)
        $categories = \Illuminate\Support\Facades\Cache::remember('categories_all', 60 * 60, function () {
            return Category::all();
        });

        $locations = \Illuminate\Support\Facades\Cache::remember('locations_all', 60 * 60, function () {
            return Location::all();
        });

        $query = Item::with(['category', 'location']);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        } else {
            $query->where('is_active', true);
        }

        // Location-based Access Check
        $user = auth()->user();
        if (! $user->isRoot()) {
            $authorizedLocations = $user->authorizedLocations();
            if ($authorizedLocations->isNotEmpty()) {
                $query->whereIn('location_id', $authorizedLocations->pluck('id'));
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('uqcode', 'LIKE', "%$search%")
                    ->orWhere('condition', 'LIKE', "%$search%")
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('location', function ($lq) use ($search) {
                        $lq->where('name', 'LIKE', "%$search%");
                    });
            });
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->input('show_destroyed') !== '1') {
            $query->where('condition', '!=', 'dimusnahkan');
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('service_status')) {
            $status = $request->service_status;
            if ($status == 'tidak_perlu') {
                $query->where('service_required', false);
            } else {
                $query->where('service_required', true);
                $query->where('condition', '!=', 'perbaikan');
                $query->whereNotNull('service_interval_days');

                $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";
                $today = "date('now')";

                if ($status == 'kelewatan') {
                    $query->whereRaw("$rawNextDate < $today");
                } elseif ($status == 'akan_datang') {
                    $query->whereRaw("$rawNextDate >= $today");
                } elseif ($status == 'jatuh_tempo') {
                    $query->whereRaw("$rawNextDate = $today");
                }
            }
        }

        $sort = $request->input('sort', 'newest');
        if ($sort == 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort == 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort == 'name_asc') {
            $query->orderBy('name', 'asc');
        }

        $setting = \App\Models\Setting::first();
        $defaultPagination = $setting->default_pagination ?? 15;

        $perPage = $request->input('per_page', $defaultPagination);
        $enablePagination = $request->input('pagination', 'on') === 'on';

        if ($enablePagination) {
            $items = $query->paginate($perPage)->withQueryString();
        } else {
            $items = $query->get();
            if ($items->count() > 50) {
                session()->flash('warning', 'Peringatan: Menampilkan data dalam jumlah besar tanpa pagination dapat menurunkan performa. Sangat direkomendasikan untuk mengaktifkan kembali pagination.');
            }
        }

        // $categories and $locations are already loaded and cached above
        // Filter locations based on user permissions if needed
        $displayLocations = $locations;
        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $displayLocations = $authLocs;
            }
        }

        return view('items.index', [
            'items' => $items,
            'categories' => $categories,
            'locations' => $displayLocations,
            'enablePagination' => $enablePagination,
        ]);
    }

    public function create(Request $request)
    {
        $categories = \Illuminate\Support\Facades\Cache::remember('categories_all', 60 * 60, function () {
            return Category::all();
        });

        $user = auth()->user();
        $allLocations = \Illuminate\Support\Facades\Cache::remember('locations_all', 60 * 60, function () {
            return Location::all();
        });

        $locations = $user->authorizedLocations();
        if ($locations->isEmpty()) {
            $locations = $allLocations;
        }

        $preselectedGroup = null;
        if ($request->filled('group_id')) {
            $preselectedGroup = ItemType::find($request->group_id);
        }

        return view('items.create', compact('categories', 'locations', 'preselectedGroup'));
    }

    private function getNameCode($itemName)
    {
        $itemType = \App\Models\ItemType::where('name', $itemName)->first();
        if ($itemType) {
            return $itemType->unique_code;
        }

        return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $itemName));
    }

    private function generateUqcode($locationId, $categoryId, $acquisitionDate, $itemName)
    {
        $location = Location::findOrFail($locationId);
        $category = Category::findOrFail($categoryId);
        $year = \Carbon\Carbon::parse($acquisitionDate)->format('Y');

        $nameCode = $this->getNameCode($itemName);

        // Count items with same Location, Category AND Name Code
        // We use the generated uqcode prefix to find similar items
        $prefix = sprintf('%s.%s.%s.', $location->unique_code, $category->unique_code, $nameCode);
        $count = Item::where('uqcode', 'LIKE', $prefix.'%')->count() + 1;

        $serial = str_pad($count, 3, '0', STR_PAD_LEFT);
        $uqcode = sprintf('%s%s.%s', $prefix, $serial, $year);

        while (Item::where('uqcode', $uqcode)->exists()) {
            $count++;
            $serial = str_pad($count, 3, '0', STR_PAD_LEFT);
            $uqcode = sprintf('%s%s.%s', $prefix, $serial, $year);
        }

        return $uqcode;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string',
            'group_id'             => 'nullable|exists:item_types,id',
            'category_id'          => 'required|exists:categories,id',
            'location_id'          => 'required|exists:locations,id',
            'condition'            => 'required|in:baik,rusak,perbaikan,dimusnahkan',
            'photo'                => 'nullable|image',
            'service_interval_days'=> 'nullable|integer',
            'acquisition_date'     => 'required|date',
            'service_required'     => 'boolean',
            'quantity'             => 'nullable|integer|min:1|max:100',
        ]);

        // Resolve group from group_id or name
        if (!empty($validated['group_id'])) {
            $group = ItemType::find($validated['group_id']);
            if ($group) {
                $validated['name'] = $group->name;
            }
        } else {
            // Try to find or create group by name
            $group = ItemType::where('name', $validated['name'])->first();
            if ($group) {
                $validated['group_id'] = $group->id;
            }
        }

        $quantity = $request->input('quantity', 1);
        $createdItems = [];
        $firstUqcode = null;

        // Handle photo upload once if possible, or copy per item?
        // Since we are creating multiple records, we should probably upload once and reuse the path
        // BUT standard implementation would likely be one photo file for all identical items if they are physically identical types.
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $imageService = app(ImageOptimizationService::class);
            $photoPath = $imageService->optimizeAndStore(
                $request->file('photo'),
                'items',
                ['quality' => 80, 'max_width' => 1200, 'max_height' => 1200]
            );
        }

        $validated['service_required'] = $request->has('service_required');
        $validated['is_active'] = true;

        if ($validated['service_required']) {
            $validated['last_service_date'] = $validated['acquisition_date'];
        }

        // Remove photo and quantity from validated data for the loop
        unset($validated['photo']);
        unset($validated['quantity']);

        // Add photo path if it exists
        if ($photoPath) {
            $validated['photo_path'] = $photoPath;
        }

        for ($i = 0; $i < $quantity; $i++) {
            // Generate UQ Code for each item (serial will increment)
            $code = $this->generateUqcode(
                $validated['location_id'],
                $validated['category_id'],
                $validated['acquisition_date'],
                $validated['name']
            );

            $itemData = $validated;
            $itemData['uqcode'] = $code;

            $item = Item::create($itemData);
            $createdItems[] = $item->id;

            if ($i === 0) {
                $firstUqcode = $code;
            }
        }

        if ($request->input('auto_qr') == '1' && count($createdItems) > 0) {
            $format = count($createdItems) > 1 ? 'zip' : 'img';
            $msg = $quantity > 1
                ? "$quantity Barang berhasil ditambahkan. Unduhan QR ($format) akan segera dimulai."
                : 'Barang berhasil ditambahkan. Unduhan QR akan segera dimulai.';

            return redirect()->route('items.index')
                ->with('success', $msg)
                ->with('trigger_download_qr', [
                    'item_ids' => $createdItems,
                    'format' => $format,
                ]);
        }

        $msg = $quantity > 1
            ? "$quantity Barang berhasil ditambahkan. Kode awal: $firstUqcode"
            : "Barang berhasil ditambahkan. Kode: $firstUqcode";

        return redirect()->route('items.index')->with('success', $msg);
    }

    public function quickQr(Item $item)
    {
        return redirect()->route('qr.generate', [
            'item_ids' => [$item->id],
            'format' => 'img',
        ]);
    }

    public function downloadQr(Item $item)
    {
        return view('items.download_qr', [
            'item' => $item,
            'title' => 'Mengunduh Label QR',
            'message' => 'Gambar label untuk '.$item->name.' sedang disiapkan.',
            'downloadUrl' => route('qr.download_file'),
            'redirectUrl' => route('items.index'),
            'method' => 'POST',
            'params' => [
                'item_ids' => [$item->id],
                'format' => 'img',
            ],
        ]);
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $user = auth()->user();
        if (! $user->canAccessLocation($item->location_id)) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        $categories = Category::all();
        $locations = $user->authorizedLocations();
        if ($locations->isEmpty()) {
            $locations = Location::all();
        }

        if (request()->ajax()) {
            return view('items.edit_partial', compact('item', 'categories', 'locations'))->render();
        }

        return view('items.edit', compact('item', 'categories', 'locations'));
    }

    public function update(Request $request, Item $item)
    {
        if ($item->condition == 'perbaikan') {
            $request->merge([
                'category_id' => $item->category_id,
                'location_id' => $item->location_id,
                'condition' => 'perbaikan',
                'acquisition_date' => $item->acquisition_date->format('Y-m-d'),
            ]);
        }

        $validated = $request->validate([
            'name'                 => 'required|string',
            'group_id'             => 'nullable|exists:item_types,id',
            'category_id'          => 'required|exists:categories,id',
            'location_id'          => 'required|exists:locations,id',
            'condition'            => 'required|in:baik,rusak,perbaikan,dimusnahkan',
            'photo'                => 'nullable|image',
            'service_interval_days'=> 'nullable|integer',
            'acquisition_date'     => 'required|date',
            'service_required'     => 'boolean',
        ]);

        // Resolve group from group_id or name
        if (!empty($validated['group_id'])) {
            $group = ItemType::find($validated['group_id']);
            if ($group) {
                $validated['name'] = $group->name;
            }
        } else {
            $group = ItemType::where('name', $validated['name'])->first();
            if ($group) {
                $validated['group_id'] = $group->id;
            }
        }

        if ($item->location_id != $validated['location_id'] || $item->category_id != $validated['category_id'] || $item->name != $validated['name']) {
            $oldUqcode = $item->uqcode;
            $newUqcode = $this->generateUqcode(
                $validated['location_id'],
                $validated['category_id'],
                $validated['acquisition_date'],
                $validated['name']
            );

            $validated['uqcode'] = $newUqcode;

            // Only create history if code actually changed (it should, but just in case)
            if ($oldUqcode !== $newUqcode) {
                ItemHistory::create([
                    'item_id' => $item->id,
                    'old_uqcode' => $oldUqcode,
                    'new_uqcode' => $newUqcode,
                    'reason' => 'Location, Category, or Name changed',
                ]);
            }
        }

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($item->photo_path) {
                $imageService = app(ImageOptimizationService::class);
                $imageService->delete($item->photo_path);
            }
            $imageService = app(ImageOptimizationService::class);
            $validated['photo_path'] = $imageService->optimizeAndStore(
                $request->file('photo'),
                'items',
                ['quality' => 80, 'max_width' => 1200, 'max_height' => 1200]
            );
        }

        $validated['service_required'] = $request->boolean('service_required');

        // Remove photo from validated data as it's not a database column
        unset($validated['photo']);

        $item->update($validated);

        if ($request->input('auto_qr') == '1') {
            return redirect()->route('items.download_qr', $item->id)
                ->with('success', 'Item updated successfully.');
        }

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->update(['is_active' => false]);

        return redirect()->route('items.index')->with('success', 'Item removed from active list (stored in history).');
    }

    public function getNextSerial(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'category_id' => 'required|exists:categories,id',
            'name'        => 'nullable|string',
            'group_id'    => 'nullable|exists:item_types,id',
        ]);

        $itemName = $request->input('name', '');

        // If group_id provided, use group name
        if ($request->filled('group_id')) {
            $group = ItemType::find($request->group_id);
            if ($group) {
                $itemName = $group->name;
            }
        }

        $location = Location::find($request->location_id);
        $category = Category::find($request->category_id);

        $nameCode = $this->getNameCode($itemName);

        $prefix = sprintf('%s.%s.%s.', $location->unique_code, $category->unique_code, $nameCode);
        $count = Item::where('uqcode', 'LIKE', $prefix . '%')->count() + 1;

        return response()->json([
            'serial'    => str_pad($count, 3, '0', STR_PAD_LEFT),
            'name_code' => $nameCode,
        ]);
    }

    public function searchNames(Request $request)
    {
        $search = $request->input('q');

        // 1. Search Standardized Item Types
        $standardQuery = \App\Models\ItemType::select('name')
            ->withCount('items');

        if ($search) {
            $standardQuery->where('name', 'LIKE', "%$search%");
        }

        $standardItems = $standardQuery->limit(5)->get()->map(function ($type) {
            return [
                'name' => $type->name,
                'count' => $type->items_count,
                'is_standard' => true,
            ];
        });

        // 2. Search Existing Items (as fallback/supplement)
        $user = auth()->user();
        $itemQuery = Item::select('name')
            ->selectRaw('COUNT(*) as total_count')
            ->where('is_active', true);

        // Location-based Access Check
        if (! $user->isRoot()) {
            $authorizedLocations = $user->authorizedLocations();
            if ($authorizedLocations->isNotEmpty()) {
                $itemQuery->whereIn('location_id', $authorizedLocations->pluck('id'));
            }
        }

        if ($search) {
            $itemQuery->where('name', 'LIKE', "%$search%");
        }

        // Exclude names already found in standard items to avoid duplicates
        if ($standardItems->isNotEmpty()) {
            $itemQuery->whereNotIn('name', $standardItems->pluck('name'));
        }

        $existingItems = $itemQuery->groupBy('name')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->total_count,
                    'is_standard' => false,
                ];
            });

        return response()->json($standardItems->merge($existingItems));
    }
}
