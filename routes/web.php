<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportLayoutController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::middleware(['permission:access_reports'])->group(function () {
        Route::get('/reports', [ReportController::class, 'menu'])->name('reports.menu');
        
        // Inventory Reports
        Route::post('/reports/inventory', [ReportController::class, 'generate'])->name('reports.inventory.generate');
        Route::post('/reports/inventory/download', [ReportController::class, 'downloadInventory'])->name('reports.inventory.download_file');
        
        // Service Reports
        Route::post('/reports/services', [ReportController::class, 'generate'])->name('reports.services.generate')->defaults('type', 'services');
        Route::post('/reports/services/download', [ReportController::class, 'downloadServices'])->name('reports.services.download_file');
        
        // Live Preview
        Route::post('/reports/preview', [ReportController::class, 'preview'])->name('reports.preview');

        Route::get('/reports/layout/{type}', [ReportLayoutController::class, 'edit'])->name('reports.layout.edit');
        Route::post('/reports/layout/{type}', [ReportLayoutController::class, 'save'])->name('reports.layout.save');
    });

});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $today = date('Y-m-d');
        $rawNextDate = "date(COALESCE(last_service_date, acquisition_date), '+' || service_interval_days || ' days')";

        $user = auth()->user();
        $authLocIds = [];
        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $authLocIds = $authLocs->pluck('id');
            }
        }

        $itemQuery = \App\Models\Item::query();
        $serviceQuery = \App\Models\Service::query();

        if (! empty($authLocIds)) {
            $itemQuery->whereIn('location_id', $authLocIds);
            $serviceQuery->whereHas('item', function ($q) use ($authLocIds) {
                $q->whereIn('location_id', $authLocIds);
            });
        }

        $data = [
            'total_items' => (clone $itemQuery)->count(),
            'total_categories' => \App\Models\Category::count(),
            'total_locations' => ! empty($authLocIds) ? count($authLocIds) : \App\Models\Location::count(),
            'total_dimusnahkan' => (clone $itemQuery)->where('condition', 'dimusnahkan')->count(),
            'total_perbaikan' => (clone $itemQuery)->where('condition', 'perbaikan')->count(),

            // Terlambat
            'overdue_count' => (clone $itemQuery)->where('is_active', true)
                ->where('service_required', true)
                ->where('condition', '!=', 'perbaikan')
                ->whereRaw("$rawNextDate < ?", [$today])
                ->count(),
            'overdue_latest' => (clone $itemQuery)->where('is_active', true)
                ->where('service_required', true)
                ->where('condition', '!=', 'perbaikan')
                ->whereRaw("$rawNextDate < ?", [$today])
                ->latest()->first(),

            // Akan datang
            'upcoming_count' => (clone $itemQuery)->where('is_active', true)
                ->where('service_required', true)
                ->where('condition', '!=', 'perbaikan')
                ->whereRaw("$rawNextDate >= ?", [$today])
                ->count(),
            'upcoming_latest' => (clone $itemQuery)->where('is_active', true)
                ->where('service_required', true)
                ->where('condition', '!=', 'perbaikan')
                ->whereRaw("$rawNextDate >= ?", [$today])
                ->latest()->first(),

            // Dalam Servis
            'in_service_count' => (clone $serviceQuery)->whereNull('date_out')->count(),
            'in_service_latest' => (clone $serviceQuery)->with('item')->whereNull('date_out')->latest()->first(),
        ];

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris Management';

        return view('welcome', compact('data', 'appName'));
    });

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware(['permission:access_items'])->group(function () {
        Route::resource('items', ItemController::class);
        Route::get('items/{item}/quick-qr', [ItemController::class, 'quickQr'])->name('items.quick_qr');
        Route::get('items/{item}/download-qr', [ItemController::class, 'downloadQr'])->name('items.download_qr');
        Route::get('api/items/next-serial', [ItemController::class, 'getNextSerial'])->name('api.items.next-serial');
        Route::get('api/items/search-names', [ItemController::class, 'searchNames'])->name('api.items.search-names');
        Route::get('qr', [App\Http\Controllers\QrController::class, 'index'])->name('qr.index');
        Route::match(['get', 'post'], 'qr/generate', [App\Http\Controllers\QrController::class, 'generate'])->name('qr.generate');
        Route::post('qr/download-file', [App\Http\Controllers\QrController::class, 'downloadFile'])->name('qr.download_file');
    });

    Route::middleware(['permission:access_services'])->group(function () {
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('items/{item}/service', [ServiceController::class, 'create'])->name('items.service.create');
        Route::post('items/{item}/service/confirm', [ServiceController::class, 'confirm'])->name('items.service.confirm');
        Route::post('items/{item}/service/store', [ServiceController::class, 'store'])->name('items.service.store');
        Route::get('services/{service}/finish', [ServiceController::class, 'finishForm'])->name('services.finish.form');
        Route::post('services/{service}/finish', [ServiceController::class, 'finish'])->name('services.finish');
        Route::post('services/{service}/fails', [ServiceController::class, 'fails'])->name('services.fails');
    });

    Route::middleware(['permission:access_categories'])->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::get('categories/{category}/items', [CategoryController::class, 'getItems'])->name('categories.items');
    });

    Route::middleware(['permission:access_locations'])->group(function () {
        Route::resource('locations', LocationController::class);
        Route::get('locations/{location}/items', [LocationController::class, 'getItems'])->name('locations.items');
    });

    Route::middleware(['permission:access_items'])->group(function () {
        Route::resource('item-types', ItemTypeController::class);
        Route::get('api/item-types/list', [ItemTypeController::class, 'apiList'])->name('api.item-types.list');
        Route::post('api/item-types/save', [ItemTypeController::class, 'apiSave'])->name('api.item-types.save');
        Route::get('api/item-types/{itemType}', [ItemTypeController::class, 'apiGet'])->name('api.item-types.get');
    });

    Route::middleware(['permission:access_settings'])->group(function () {
        Route::get('settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::get('tasks', [App\Http\Controllers\BackgroundTaskController::class, 'index'])->name('tasks.index');
    });

    Route::middleware(['permission:access_users'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
    });
});
