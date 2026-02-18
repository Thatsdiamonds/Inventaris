<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemType::withCount('items')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('unique_code', 'LIKE', "%$search%");
        }

        $itemTypes = $query->paginate(15)->withQueryString();

        return view('item_types.index', compact('itemTypes'));
    }

    public function create()
    {
        return view('item_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|unique:item_types,name',
            'unique_code' => 'required|string|unique:item_types,unique_code|max:10',
            'description' => 'nullable|string',
        ]);

        ItemType::create($validated);

        return redirect()->route('item-types.index')->with('success', 'Grup nama aset berhasil ditambahkan.');
    }

    public function edit(ItemType $itemType)
    {
        $itemCount = $itemType->items()->count();
        return view('item_types.edit', compact('itemType', 'itemCount'));
    }

    public function update(Request $request, ItemType $itemType)
    {
        $validated = $request->validate([
            'name'        => 'required|string|unique:item_types,name,' . $itemType->id,
            'unique_code' => 'required|string|unique:item_types,unique_code,' . $itemType->id . '|max:10',
            'description' => 'nullable|string',
        ]);

        $oldCode = $itemType->unique_code;
        $newCode = $validated['unique_code'];

        DB::transaction(function () use ($itemType, $validated, $oldCode, $newCode) {
            $itemType->update($validated);

            // If unique_code changed, update all related items' uqcodes
            if ($oldCode !== $newCode) {
                $items = Item::where('group_id', $itemType->id)->get();
                foreach ($items as $item) {
                    $newUqcode = str_replace('.' . $oldCode . '.', '.' . $newCode . '.', $item->uqcode);
                    if ($newUqcode !== $item->uqcode) {
                        $item->update(['uqcode' => $newUqcode]);
                    }
                }
            }
        });

        return redirect()->route('item-types.index')->with('success', 'Grup nama aset berhasil diperbarui.');
    }

    public function destroy(ItemType $itemType)
    {
        // Detach items from this group before deleting
        Item::where('group_id', $itemType->id)->update(['group_id' => null]);
        $itemType->delete();

        return redirect()->route('item-types.index')->with('success', 'Grup nama aset berhasil dihapus.');
    }

    // ─── API Endpoints ────────────────────────────────────────────────────────

    /**
     * API: List/search groups with real-time counts.
     */
    public function apiList(Request $request)
    {
        $search = $request->input('q', '');
        $query = ItemType::withCount('items')->orderBy('name');

        if ($search) {
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('unique_code', 'LIKE', "%$search%");
        }

        $groups = $query->limit(30)->get()->map(function ($g) {
            return [
                'id'          => $g->id,
                'name'        => $g->name,
                'unique_code' => $g->unique_code,
                'item_count'  => $g->items_count,
                'latest_code' => $g->items()->orderBy('created_at', 'desc')->value('uqcode'),
            ];
        });

        return response()->json($groups);
    }

    /**
     * API: Save (create or update) a group.
     */
    public function apiSave(Request $request)
    {
        $id = $request->input('id');

        if ($id) {
            $itemType = ItemType::findOrFail($id);
            $validated = $request->validate([
                'name'        => 'required|string|unique:item_types,name,' . $id,
                'unique_code' => 'required|string|unique:item_types,unique_code,' . $id . '|max:10',
                'description' => 'nullable|string',
            ]);

            $oldCode = $itemType->unique_code;
            $newCode = $validated['unique_code'];

            DB::transaction(function () use ($itemType, $validated, $oldCode, $newCode) {
                $itemType->update($validated);
                if ($oldCode !== $newCode) {
                    $items = Item::where('group_id', $itemType->id)->get();
                    foreach ($items as $item) {
                        $newUqcode = str_replace('.' . $oldCode . '.', '.' . $newCode . '.', $item->uqcode);
                        if ($newUqcode !== $item->uqcode) {
                            $item->update(['uqcode' => $newUqcode]);
                        }
                    }
                }
            });

            $itemType->refresh();
            return response()->json([
                'success' => true,
                'group'   => [
                    'id'          => $itemType->id,
                    'name'        => $itemType->name,
                    'unique_code' => $itemType->unique_code,
                    'item_count'  => $itemType->items()->count(),
                    'latest_code' => $itemType->items()->orderBy('created_at', 'desc')->value('uqcode'),
                ],
            ]);
        }

        // Create
        $validated = $request->validate([
            'name'        => 'required|string|unique:item_types,name',
            'unique_code' => 'required|string|unique:item_types,unique_code|max:10',
            'description' => 'nullable|string',
        ]);

        $itemType = ItemType::create($validated);

        return response()->json([
            'success' => true,
            'group'   => [
                'id'          => $itemType->id,
                'name'        => $itemType->name,
                'unique_code' => $itemType->unique_code,
                'item_count'  => 0,
                'latest_code' => null,
            ],
        ]);
    }

    /**
     * API: Get a single group's info.
     */
    public function apiGet(ItemType $itemType)
    {
        return response()->json([
            'id'          => $itemType->id,
            'name'        => $itemType->name,
            'unique_code' => $itemType->unique_code,
            'description' => $itemType->description,
            'item_count'  => $itemType->items()->count(),
            'latest_code' => $itemType->items()->orderBy('created_at', 'desc')->value('uqcode'),
        ]);
    }
}
