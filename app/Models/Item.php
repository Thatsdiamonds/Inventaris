<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'uqcode',
        'name',
        'category_id',
        'location_id',
        'condition',
        'photo_path',
        'service_interval_days',
        'service_required',
        'last_service_date',
        'acquisition_date',
        'is_active',
    ];

    protected $casts = [
        'service_required' => 'boolean',
        'is_active' => 'boolean',
        'last_service_date' => 'date',
        'acquisition_date' => 'date',
    ];

    public function getNextServiceDateAttribute()
    {
        if (!$this->service_required || !$this->service_interval_days) {
            return null;
        }

        $baseDate = $this->last_service_date ?? $this->acquisition_date;

        if (!$baseDate) {
            return null;
        }

        // Ensure we are working with Carbon instances and set to start of day for accurate diffs
        return \Carbon\Carbon::parse($baseDate)->startOfDay()->addDays($this->service_interval_days);
    }

    public function getServiceStatusLabelAttribute()
    {
        if (!$this->service_required) {
            return 'Tidak Perlu';
        }

        $nextService = $this->next_service_date;

        if (!$nextService) {
            return 'Menunggu Data';
        }

        $today = now()->startOfDay();
        $nextService = $nextService->startOfDay();

        if ($today->gt($nextService)) {
            $days = $today->diffInDays($nextService);
            return "Kelewatan ($days Hari)";
        }

        $days = $today->diffInDays($nextService);
        
        if ($days == 0) {
            return "Jatuh Tempo Hari Ini";
        }
        
        return "Akan Datang ($days Hari lagi)";
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function disposal()
    {
        return $this->hasOne(Disposal::class);
    }

    public function histories()
    {
        return $this->hasMany(ItemHistory::class);
    }
}
