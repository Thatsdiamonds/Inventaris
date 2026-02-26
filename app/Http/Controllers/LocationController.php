<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::withCount('items')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('unique_code', 'LIKE', "%$search%");
        }

        $locations = $query->paginate(10)->withQueryString();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        
        if ($request->has('auto_code')) {
            $data['unique_code'] = $this->sanitizeToCode($request->name);
        }

        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string',
            'unique_code' => 'required|string|min:4|max:16|unique:locations,unique_code',
            'description' => 'nullable|string',
        ]);

        Location::create($validated);
        
        \Illuminate\Support\Facades\Cache::forget('locations_all');

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    private function sanitizeToCode($name)
    {
        $name = preg_replace('/[^A-Za-z0-9\s]/', '', $name);
        return str_replace(' ', '', ucwords(strtolower($name)));
    }

    public function show(Location $location)
    {
        return view('locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        $itemCount = \App\Models\Item::where('location_id', $location->id)->count();
        if (request()->ajax()) {
            return view('locations.edit_partial', compact('location', 'itemCount'))->render();
        }
        return view('locations.edit', compact('location', 'itemCount'));
    }

    public function update(Request $request, Location $location)
    {
        $oldCode = $location->unique_code;
        $data = $request->all();
        if ($request->has('auto_code')) {
            $data['unique_code'] = $this->sanitizeToCode($request->name);
        }
        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string',
            'unique_code' => 'required|string|min:2|max:6|unique:locations,unique_code,' . $location->id,
            'description' => 'nullable|string',
        ]);

        $location->update($validated);

        // Cascade update items if unique_code changed
        if ($oldCode !== $location->unique_code) {
            $excludedItems = $request->input('excluded_items', []);
            
            \Illuminate\Support\Facades\Cache::forget('locations_all');

            return redirect()->route('items.process_update', [
                'type' => 'location',
                'id' => $location->id,
                'old_code' => $oldCode,
                'new_code' => $location->unique_code,
                'excluded_items' => $excludedItems,
                'redirect_url' => route('locations.index')
            ]);
        }

        \Illuminate\Support\Facades\Cache::forget('locations_all');
        
        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        \Illuminate\Support\Facades\Cache::forget('locations_all');
        
        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }

    public function getItems(Location $location)
    {
        $items = \App\Models\Item::where('location_id', $location->id)
            ->select('id', 'name', 'uqcode')
            ->get();
        return response()->json($items);
    }
}
