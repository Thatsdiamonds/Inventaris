<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Service;

class ReportService
{
    /**
     * Get inventory items based on filters
     */
    public function getInventoryData(array $filters, $limit = null)
    {
        $query = Item::with(['location', 'category']);

        // Permission check (authorized locations)
        $user = auth()->user();
        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $query->whereIn('location_id', $authLocs->pluck('id'));
            }
        }

        if (isset($filters['scope']) && $filters['scope'] === 'barang' && ! empty($filters['item_id'])) {
            $query->where('id', $filters['item_id']);
        }

        if (! empty($filters['locations'])) {
            $query->whereIn('location_id', (array) $filters['locations']);
        }

        if (! empty($filters['categories'])) {
            $query->whereIn('category_id', (array) $filters['categories']);
        }

        if (! empty($filters['condition']) && $filters['condition'] !== 'all') {
            $query->where('condition', $filters['condition']);
        }

        if (! empty($filters['from']) && ! empty($filters['to'])) {
            $query->whereBetween('created_at', [
                $filters['from'].' 00:00:00',
                $filters['to'].' 23:59:59',
            ]);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get service records based on filters
     */
    public function getServiceData(array $filters, $limit = null)
    {
        $query = Service::with('item.location');

        // Permission check
        $user = auth()->user();
        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $query->whereHas('item', function ($q) use ($authLocs) {
                    $q->whereIn('location_id', $authLocs->pluck('id'));
                });
            }
        }

        if (! empty($filters['vendor']) && $filters['vendor'] !== 'all') {
            $query->where('vendor', $filters['vendor']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'proses') {
                $query->whereNull('date_out');
            } elseif ($filters['status'] === 'selesai') {
                $query->whereNotNull('date_out');
            }
        }

        if (! empty($filters['from']) && ! empty($filters['to'])) {
            $query->whereBetween('date_in', [$filters['from'], $filters['to']]);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get summary for inventory report
     */
    public function getInventorySummary($items)
    {
        $counts = $items->countBy('condition');

        return [
            'total' => $items->count(),
            'kategori' => $items->pluck('category_id')->unique()->count(),
            'baik' => $counts->get('baik', 0),
            'rusak' => $counts->get('rusak', 0),
            'perbaikan' => $counts->get('perbaikan', 0),
            'dimusnahkan' => $counts->get('dimusnahkan', 0),
        ];
    }

    /**
     * Get summary for service report
     */
    public function getServiceSummary($services)
    {
        $total = $services->count();
        $selesai = $services->whereNotNull('date_out')->count();

        return [
            'total' => $total,
            'proses' => $total - $selesai,
            'selesai' => $selesai,
            'total_cost' => $services->sum('cost'),
        ];
    }
}
