<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        if (! $settings) {
            $settings = Setting::create([
                'nama_gereja' => 'Inventaris Management',
                'maintenance_threshold' => 30,
                'currency' => 'IDR',
            ]);
        }

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_gereja' => 'required|string|max:255',
            'maintenance_threshold' => 'required|integer|min:1',
            'alamat' => 'nullable|string',
            'auto_download_after_add' => 'boolean',
            'auto_download_after_edit' => 'boolean',
            'default_pagination' => 'required|integer|min:1|max:500',
        ]);

        $validated['auto_download_after_add'] = $request->has('auto_download_after_add');
        $validated['auto_download_after_edit'] = $request->has('auto_download_after_edit');

        $setting = Setting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
