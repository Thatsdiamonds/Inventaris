<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        
        if ($request->has('auto_code')) {
            $data['unique_code'] = $this->sanitizeToCode($request->name);
        }

        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name',
            'unique_code' => 'required|string|min:4|max:16|unique:categories,unique_code',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    private function sanitizeToCode($name)
    {
        // Remove special characters, keep spaces for PascalCase conversion
        $name = preg_replace('/[^A-Za-z0-9\s]/', '', $name);
        // Convert to PascalCase (Spasi dihapus, Setiap awal kata menggunakan huruf kapital)
        return str_replace(' ', '', ucwords(strtolower($name)));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $itemCount = \App\Models\Item::where('category_id', $category->id)->count();
        if (request()->ajax()) {
            return view('categories.edit_partial', compact('category', 'itemCount'))->render();
        }
        return view('categories.edit', compact('category', 'itemCount'));
    }

    public function update(Request $request, Category $category)
    {
        $oldCode = $category->unique_code;
        $data = $request->all();
        if ($request->has('auto_code')) {
            $data['unique_code'] = $this->sanitizeToCode($request->name);
        }
        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'unique_code' => 'required|string|min:4|max:16|unique:categories,unique_code,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        // Cascade update items if unique_code changed
        if ($oldCode !== $category->unique_code) {
            $excludedItems = $request->input('excluded_items', []);
            $itemsQuery = \App\Models\Item::where('category_id', $category->id);
            
            if (!empty($excludedItems)) {
                $itemsQuery->whereNotIn('id', $excludedItems);
            }

            $count = $itemsQuery->count();

            if ($count > 0) {
                if ($count > 50) {
                    // Background process
                    $task = \App\Models\BackgroundTask::create([
                        'name' => 'Update UQCode Kategori: ' . $category->name . ' (Partial)',
                        'total_items' => $count,
                        'status' => 'pending'
                    ]);

                    // Modify Job to support excluded items if needed, or just pass the query refinement
                    // For now, if excluded items exist, we might need a different job or pass IDs
                    if (!empty($excludedItems)) {
                         // We pass excluded IDs to the job
                         \App\Jobs\UpdateItemUqcodesJob::dispatch('category', $category->id, $category->unique_code, $task->id, $excludedItems);
                    } else {
                         \App\Jobs\UpdateItemUqcodesJob::dispatch('category', $category->id, $category->unique_code, $task->id);
                    }

                    return redirect()->route('categories.index')
                        ->with('success', 'Pembaruan kode unik kategori sedang diproses di latar belakang untuk ' . $count . ' barang.');
                } else {
                    // Synchronous process
                    $items = $itemsQuery->get();
                    foreach ($items as $item) {
                        $parts = explode('.', $item->uqcode);
                        if (count($parts) === 4) {
                            $oldUqcode = $item->uqcode;
                            $parts[1] = $category->unique_code; 
                            $newUqcode = implode('.', $parts);
                            $item->update(['uqcode' => $newUqcode]);
                            \App\Models\ItemHistory::create([
                                'item_id' => $item->id,
                                'old_uqcode' => $oldUqcode,
                                'new_uqcode' => $newUqcode,
                                'reason' => 'Category unique_code changed (Synchronous Partial)',
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function getItems(Category $category)
    {
        $user = auth()->user();
        $query = \App\Models\Item::where('category_id', $category->id);
        
        if (!$user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $query->whereIn('location_id', $authLocs->pluck('id'));
            }
        }

        $items = $query->select('id', 'name', 'uqcode')->get();
        return response()->json($items);
    }
}
