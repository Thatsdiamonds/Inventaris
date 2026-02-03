<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ImageOptimizationService;
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
            'church_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'alamat' => 'nullable|string',
            'auto_download_after_add' => 'boolean',
            'auto_download_after_edit' => 'boolean',
            'default_pagination' => 'required|integer|min:1|max:500',
        ]);

        $validated['auto_download_after_add'] = $request->has('auto_download_after_add');
        $validated['auto_download_after_edit'] = $request->has('auto_download_after_edit');

        $setting = Setting::first();
        
        if ($request->hasFile('church_photo')) {
            $imageService = app(ImageOptimizationService::class);
            
            // Delete old photo if exists
            if ($setting && $setting->church_photo_path) {
                $imageService->delete($setting->church_photo_path);
            }
            
            // Optimize and store new photo
            $validated['church_photo_path'] = $imageService->optimizeAndStore(
                $request->file('church_photo'),
                'settings',
                ['quality' => 85, 'max_width' => 800, 'max_height' => 800]
            );
        }

        // Remove church_photo from validated data as it's not a database column
        unset($validated['church_photo']);

        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        \Illuminate\Support\Facades\Cache::forget('app_locale');
        \Illuminate\Support\Facades\Cache::forget('church_settings');

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
