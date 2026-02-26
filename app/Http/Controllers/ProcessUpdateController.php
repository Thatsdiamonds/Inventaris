<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\Location;
use Illuminate\Http\Request;

class ProcessUpdateController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');
        $oldCode = $request->query('old_code');
        $newCode = $request->query('new_code');
        $excludedItems = $request->query('excluded_items', []);

        if (!$type || !$id) {
            return redirect()->route('items.index')->with('error', 'Parameter tidak valid.');
        }

        $typeLabel = ($type === 'category' ? 'Kategori' : ($type === 'location' ? 'Lokasi' : 'Nama Aset'));
        $title = "Memperbarui Kode Barang";
        $message = "Menyesuaikan kode unik barang karena perubahan pada {$typeLabel}.";

        $itemsQuery = Item::where($type . '_id', $id);
        if (!empty($excludedItems)) {
            $itemsQuery->whereNotIn('id', $excludedItems);
        }

        $total = $itemsQuery->count();

        // Determine fallback route based on type
        $fallbackRoute = 'items.index';
        if ($type === 'group') {
            $fallbackRoute = 'item-types.index';
        } elseif ($type === 'category') {
            $fallbackRoute = 'categories.index';
        } elseif ($type === 'location') {
            $fallbackRoute = 'locations.index';
        }

        // If no items to update, just finish
        if ($total === 0) {
            return redirect($request->query('redirect_url', route($fallbackRoute)))
                ->with('success', 'Pembaruan selesai. Tidak ada barang yang perlu diubah.');
        }

        return view('items.process_update', [
            'type' => $type,
            'id' => $id,
            'oldCode' => $oldCode,
            'newCode' => $newCode,
            'excludedItems' => $excludedItems,
            'total' => $total,
            'title' => $title,
            'message' => $message,
            'redirectUrl' => $request->query('redirect_url', route($fallbackRoute))
        ]);
    }

    public function step(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:category,location,group',
            'id' => 'required|integer',
            'new_code' => 'required|string',
            'old_code' => 'nullable|string',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'excluded_items' => 'nullable|array'
        ]);

        $type = $request->type;
        $id = $request->id;
        $newCodeValue = $request->new_code;
        $oldCodeValue = $request->old_code;
        $limit = $request->input('limit', 50);
        $excludedItems = $request->input('excluded_items', []);

        $itemsQuery = Item::where($type . '_id', $id);
        if (!empty($excludedItems)) {
            $itemsQuery->whereNotIn('id', $excludedItems);
        }

        $items = $itemsQuery->orderBy('id')->offset($request->input('offset', 0))->limit($limit)->get();
        $processed = 0;

        foreach ($items as $item) {
            $parts = explode('.', $item->uqcode);
            $changed = false;

            if ($type === 'location' && count($parts) >= 4) {
                if ($parts[0] !== $newCodeValue) {
                    $parts[0] = $newCodeValue;
                    $changed = true;
                }
            } elseif ($type === 'category' && count($parts) >= 4) {
                if ($parts[1] !== $newCodeValue) {
                    $parts[1] = $newCodeValue;
                    $changed = true;
                }
            } elseif ($type === 'group' && count($parts) >= 5) {
                if ($parts[2] !== $newCodeValue) {
                    $parts[2] = $newCodeValue;
                    $changed = true;
                }
            } elseif ($type === 'group' && count($parts) == 4) {
                $parts = [$parts[0], $parts[1], $newCodeValue, $parts[2], $parts[3]];
                $changed = true;
            }

            if ($changed) {
                $newUqcode = implode('.', $parts);
                if (!Item::where('uqcode', $newUqcode)->where('id', '!=', $item->id)->exists()) {
                    $item->update(['uqcode' => $newUqcode]);
                }
            }
            $processed++;
        }

        return response()->json([
            'processed' => $processed,
            'finished' => $items->count() < $limit
        ]);
    }

    public function check(Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        $newCodeValue = $request->new_code;
        $excludedItems = $request->input('excluded_items', []);

        $itemsQuery = Item::where($type . '_id', $id);
        if (!empty($excludedItems)) {
            $itemsQuery->whereNotIn('id', $excludedItems);
        }

        $items = $itemsQuery->get();
        $inconsistent = 0;

        foreach ($items as $item) {
            $parts = explode('.', $item->uqcode);
            $isMatch = false;

            if ($type === 'location' && count($parts) >= 4) {
                $isMatch = ($parts[0] === $newCodeValue);
            } elseif ($type === 'category' && count($parts) >= 4) {
                $isMatch = ($parts[1] === $newCodeValue);
            } elseif ($type === 'group' && count($parts) >= 5) {
                $isMatch = ($parts[2] === $newCodeValue);
            } elseif ($type === 'group' && count($parts) == 4) {
                $isMatch = false; // Always inconsistent if still 4 parts but group code changed
            }

            if (!$isMatch) {
                $inconsistent++;
            }
        }

        return response()->json([
            'total' => $items->count(),
            'inconsistent' => $inconsistent,
            'success' => $inconsistent === 0
        ]);
    }
}
