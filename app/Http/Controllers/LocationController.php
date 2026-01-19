<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
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
        if (request()->ajax()) {
            return view('locations.edit_partial', compact('location'))->render();
        }
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->all();
        if ($request->has('auto_code')) {
            $data['unique_code'] = $this->sanitizeToCode($request->name);
        }
        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string',
            'unique_code' => 'required|string|min:4|max:16|unique:locations,unique_code,' . $location->id,
            'description' => 'nullable|string',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
