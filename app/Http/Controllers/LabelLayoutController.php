<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabelLayout;

class LabelLayoutController extends Controller
{
    public function index()
    {
        $layouts = LabelLayout::orderBy('is_active', 'desc')->orderBy('name')->get();
        return view('qr.layouts.index', compact('layouts'));
    }

    public function create()
    {
        return view('qr.layouts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'paper_size' => 'required|string',
            'margin_top' => 'required|numeric|min:0',
            'margin_bottom' => 'required|numeric|min:0',
            'margin_left' => 'required|numeric|min:0',
            'margin_right' => 'required|numeric|min:0',
            'gap_x' => 'required|numeric|min:0',
            'gap_y' => 'required|numeric|min:0',
            'font_size' => 'required|integer|min:4',
        ]);

        if ($request->has('is_active') && $request->is_active) {
            LabelLayout::where('is_active', true)->update(['is_active' => false]);
            $validated['is_active'] = true;
        } else {
             // Ensure at least one is active if none exist
            if (LabelLayout::count() === 0) {
                $validated['is_active'] = true;
            }
        }

        LabelLayout::create($validated);

        return redirect()->route('label-layouts.index')
            ->with('success', 'Layout label berhasil dibuat.');
    }

    public function edit(string $id)
    {
        $layout = LabelLayout::findOrFail($id);
        return view('qr.layouts.edit', compact('layout'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'paper_size' => 'required|string',
            'margin_top' => 'required|numeric|min:0',
            'margin_bottom' => 'required|numeric|min:0',
            'margin_left' => 'required|numeric|min:0',
            'margin_right' => 'required|numeric|min:0',
            'gap_x' => 'required|numeric|min:0',
            'gap_y' => 'required|numeric|min:0',
            'font_size' => 'required|integer|min:4',
        ]);

        $layout = LabelLayout::findOrFail($id);

        if ($request->has('is_active') && $request->is_active) {
             LabelLayout::where('id', '!=', $id)->update(['is_active' => false]);
             $validated['is_active'] = true;
        }

        $layout->update($validated);

        return redirect()->route('label-layouts.index')
            ->with('success', 'Layout label berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $layout = LabelLayout::findOrFail($id);
        if ($layout->is_active) {
            return back()->with('error', 'Tidak dapat menghapus layout yang sedang aktif.');
        }
        $layout->delete();

        return redirect()->route('label-layouts.index')
            ->with('success', 'Layout label berhasil dihapus.');
    }

    public function activate(string $id)
    {
        $layout = LabelLayout::findOrFail($id);
        
        LabelLayout::where('is_active', true)->update(['is_active' => false]);
        $layout->update(['is_active' => true]);

        return redirect()->route('label-layouts.index')
            ->with('success', 'Layout label aktif berhasil diubah.');
    }
}
