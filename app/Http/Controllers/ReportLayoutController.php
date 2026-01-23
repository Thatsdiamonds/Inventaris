<?php

namespace App\Http\Controllers;

use App\Models\ReportLayout;
use Illuminate\Http\Request;

class ReportLayoutController extends Controller
{
    public function edit($type)
{
    $layout = ReportLayout::where('report_type', $type)->first();

    $fields = [];

    if ($layout) {
        $fields = is_array($layout->columns)
            ? $layout->columns
            : json_decode($layout->columns, true);
    }

    return view('reports.layout.editor', compact('layout', 'fields', 'type'));
}


    public function save(Request $request, $type)
    {
        $columns = json_decode($request->input('columns', '[]'), true);

        ReportLayout::updateOrCreate(
            ['report_type' => $type],
            ['columns' => json_encode($columns)]
        );

        return redirect()
            ->route('reports.menu')
            ->with('success', 'Pengaturan laporan disimpan');
    }
}
