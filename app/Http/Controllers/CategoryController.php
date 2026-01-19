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
        if (request()->ajax()) {
            return view('categories.edit_partial', compact('category'))->render();
        }
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
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

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
