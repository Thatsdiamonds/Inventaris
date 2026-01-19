<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\Location;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'location']);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        } else {
            $query->where('is_active', true);
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

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('service_status')) {
            $status = $request->service_status;
            if ($status == 'tidak_perlu') {
                $query->where('service_required', false);
            } else {
                $query->where('service_required', true);
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

        $sort = $request->input('sort', 'name');
        if ($sort == 'name_desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->input('per_page', 10);
        $enablePagination = $request->input('pagination', 'on') === 'on';

        if ($enablePagination) {
            $items = $query->paginate($perPage)->withQueryString();
        } else {
            $items = $query->get();
            if ($items->count() > 50) {
                session()->flash('warning', 'Peringatan: Menampilkan data dalam jumlah besar tanpa pagination dapat menurunkan performa. Sangat direkomendasikan untuk mengaktifkan kembali pagination.');
            }
        }

        $categories = Category::all();
        $locations = Location::all();

        return view('items.index', compact('items', 'categories', 'locations', 'enablePagination'));
    }

    public function create()
    {
        $categories = Category::all();
        $locations = Location::all();

        return view('items.create', compact('categories', 'locations'));
    }

    private function generateUqcode($locationId, $categoryId, $acquisitionDate)
    {
        $location = Location::findOrFail($locationId);
        $category = Category::findOrFail($categoryId);
        $year = \Carbon\Carbon::parse($acquisitionDate)->format('Y');

        $count = Item::where('location_id', $locationId)
            ->where('category_id', $categoryId)
            ->count() + 1;

        $serial = str_pad($count, 3, '0', STR_PAD_LEFT);
        $uqcode = sprintf('%s.%s.%s.%s', $location->unique_code, $category->unique_code, $serial, $year);

        while (Item::where('uqcode', $uqcode)->exists()) {
            $count++;
            $serial = str_pad($count, 3, '0', STR_PAD_LEFT);
            $uqcode = sprintf('%s.%s.%s.%s', $location->unique_code, $category->unique_code, $serial, $year);
        }

        return $uqcode;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id',
            'condition' => 'required|in:baik,rusak,perbaikan,dimusnahkan',
            'photo' => 'nullable|image',
            'service_interval_days' => 'nullable|integer',
            'acquisition_date' => 'required|date',
            'service_required' => 'boolean',
        ]);

        $validated['uqcode'] = $this->generateUqcode(
            $validated['location_id'],
            $validated['category_id'],
            $validated['acquisition_date']
        );

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('items', 'public');
        }

        $validated['service_required'] = $request->has('service_required');
        $validated['is_active'] = true;

        if ($validated['service_required']) {
            $validated['last_service_date'] = $validated['acquisition_date'];
        }

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Item created successfully. Code: '.$validated['uqcode']);
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = Category::all();
        $locations = Location::all();

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
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id',
            'condition' => 'required|in:baik,rusak,perbaikan,dimusnahkan',
            'photo' => 'nullable|image',
            'service_interval_days' => 'nullable|integer',
            'acquisition_date' => 'required|date',
            'service_required' => 'boolean',
        ]);

        if ($item->location_id != $validated['location_id'] || $item->category_id != $validated['category_id']) {
            $oldUqcode = $item->uqcode;
            $newUqcode = $this->generateUqcode(
                $validated['location_id'],
                $validated['category_id'],
                $validated['acquisition_date']
            );

            $validated['uqcode'] = $newUqcode;

            ItemHistory::create([
                'item_id' => $item->id,
                'old_uqcode' => $oldUqcode,
                'new_uqcode' => $newUqcode,
                'reason' => 'Location or Category changed',
            ]);
        }

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('items', 'public');
        }

        $validated['service_required'] = $request->boolean('service_required');

        $item->update($validated);

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
        ]);

        $count = Item::where('location_id', $request->location_id)
            ->where('category_id', $request->category_id)
            ->count() + 1;

        return response()->json([
            'serial' => str_pad($count, 3, '0', STR_PAD_LEFT),
        ]);
    }
}
