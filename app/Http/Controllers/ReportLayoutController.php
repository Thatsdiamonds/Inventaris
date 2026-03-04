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

        $availableFields = [
            'inventory' => [
    ['key' => 'uqcode', 'label' => 'Kode Barang'],
    ['key' => 'name', 'label' => 'Nama Barang'],
    ['key' => 'category.name', 'label' => 'Kategori'],
    ['key' => 'location.name', 'label' => 'Lokasi'],
    ['key' => 'condition', 'label' => 'Kondisi'],

    ['key' => 'acquisition_date', 'label' => 'Tanggal Perolehan'],
    ['key' => 'created_at', 'label' => 'Tanggal Inventarisasi'],
    ['key' => 'service_interval_days', 'label' => 'Maintenance (Hari)'],
    ['key' => 'last_service_date', 'label' => 'Terakhir Servis'],
],
            'qr' => [
                ['key' => 'church_name', 'label' => 'Nama Gereja'],
                ['key' => 'location.name', 'label' => 'Ruang / Lokasi'],
                ['key' => 'uqcode', 'label' => 'Kode Barang'],
                ['key' => 'created_at', 'label' => 'Tahun Inventaris'],
                ['key' => 'acquisition_date', 'label' => 'Tahun Perolehan'],
                ['key' => 'qr_code', 'label' => 'QR Code'],
            ]
        ];

        $typeFields = $availableFields[$type] ?? $availableFields['inventory'];

        return view('reports.layout.editor', compact('layout', 'fields', 'type', 'typeFields'));
    }

    public function save(Request $request, $type)
    {
        $columns = json_decode($request->input('columns', '[]'), true);
        ReportLayout::updateOrCreate(
            ['report_type' => $type],
            [
                'columns' => json_encode($columns)
            ]
        );

        return redirect()
            ->route('reports.menu')
            ->with('success', 'Pengaturan laporan disimpan');
    }
}
