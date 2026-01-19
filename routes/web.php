<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $today = date('Y-m-d');
        $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";

        $data = [
            'total_items' => \App\Models\Item::count(),
            'total_categories' => \App\Models\Category::count(),
            'total_locations' => \App\Models\Location::count(),
            'total_dimusnahkan' => \App\Models\Item::where('condition', 'dimusnahkan')->count(),
            'total_perbaikan' => \App\Models\Item::where('condition', 'perbaikan')->count(),

            // Terlambat
            'overdue_count' => \App\Models\Item::where('is_active', true)
                ->where('service_required', true)
                ->whereRaw("$rawNextDate < ?", [$today])
                ->count(),
            'overdue_latest' => \App\Models\Item::where('is_active', true)
                ->where('service_required', true)
                ->whereRaw("$rawNextDate < ?", [$today])
                ->latest()->first(),

            // Akan datang
            'upcoming_count' => \App\Models\Item::where('is_active', true)
                ->where('service_required', true)
                ->whereRaw("$rawNextDate >= ?", [$today])
                ->count(),
            'upcoming_latest' => \App\Models\Item::where('is_active', true)
                ->where('service_required', true)
                ->whereRaw("$rawNextDate >= ?", [$today])
                ->latest()->first(),

            // Dalam Servis
            'in_service_count' => \App\Models\Service::whereNull('date_out')->count(),
            'in_service_latest' => \App\Models\Service::with('item')->whereNull('date_out')->latest()->first(),
        ];

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris Management';

        return view('welcome', compact('data', 'appName'));
    });

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::resource('items', ItemController::class);
    Route::get('api/items/next-serial', [ItemController::class, 'getNextSerial'])->name('api.items.next-serial');

    // Unified Services Module
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('items/{item}/service', [ServiceController::class, 'create'])->name('items.service.create');
    Route::post('items/{item}/service/confirm', [ServiceController::class, 'confirm'])->name('items.service.confirm');
    Route::post('items/{item}/service/store', [ServiceController::class, 'store'])->name('items.service.store');
    Route::post('services/{service}/finish', [ServiceController::class, 'finish'])->name('services.finish');
    Route::post('services/{service}/fails', [ServiceController::class, 'fails'])->name('services.fails');

    Route::resource('categories', CategoryController::class);
    Route::resource('locations', LocationController::class);

    Route::get('settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // QR Generator
    Route::get('qr', [App\Http\Controllers\QrController::class, 'index'])->name('qr.index');
    Route::post('qr/generate', [App\Http\Controllers\QrController::class, 'generate'])->name('qr.generate');
});
