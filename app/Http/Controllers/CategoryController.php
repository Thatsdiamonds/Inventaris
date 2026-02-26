<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('items')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('unique_code', 'LIKE', "%$search%");
        }

        $categories = $query->paginate(10)->withQueryString();

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
            'unique_code' => 'required|string|min:2|max:6|unique:categories,unique_code',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);
        
        \Illuminate\Support\Facades\Cache::forget('categories_all');

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
            'unique_code' => 'required|string|min:2|max:6|unique:categories,unique_code,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        // Cascade update items if unique_code changed
        if ($oldCode !== $category->unique_code) {
            $excludedItems = $request->input('excluded_items', []);
            
            \Illuminate\Support\Facades\Cache::forget('categories_all');

            return redirect()->route('items.process_update', [
                'type' => 'category',
                'id' => $category->id,
                'old_code' => $oldCode,
                'new_code' => $category->unique_code,
                'excluded_items' => $excludedItems,
                'redirect_url' => route('categories.index')
            ]);
        }

        \Illuminate\Support\Facades\Cache::forget('categories_all');

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        \Illuminate\Support\Facades\Cache::forget('categories_all');

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
