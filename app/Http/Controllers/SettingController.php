<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create([
                'nama_gereja' => 'Inventaris Management',
                'maintenance_threshold' => 30,
                'currency' => 'IDR'
            ]);
        }
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_gereja' => 'required|string|max:255',
            'maintenance_threshold' => 'required|integer|min:1',
            'currency' => 'required|string|max:10',
            'alamat' => 'nullable|string',
        ]);

        $setting = Setting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
