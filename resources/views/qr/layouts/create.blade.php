@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
                <h1 class="page-title">Buat Layout Label Baru</h1>
                <p class="text-secondary">Atur dimensi label sesuai kebutuhan.</p>
            </div>
            <div class="col-md-6 col-sm-12 text-md-end">
                <a href="{{ route('label-layouts.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </div>
    </div>

    <form action="{{ route('label-layouts.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Informasi Dasar</h3>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Layout</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Label A4 Standar"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Ukuran Kertas</label>
                        <select name="paper_size" class="form-select">
                            <option value="A4" selected>A4 (210mm x 297mm)</option>
                            <option value="Letter">Letter (215.9mm x 279.4mm)</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                </div>

                <h3 class="card-title mt-4">Dimensi Label (mm)</h3>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Lebar Label</label>
                        <input type="number" step="0.1" name="width" class="form-control" value="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tinggi Label</label>
                        <input type="number" step="0.1" name="height" class="form-control" value="30" required>
                    </div>
                </div>

                <h3 class="card-title mt-4">Margin Halaman (mm)</h3>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label required">Atas</label>
                        <input type="number" step="0.1" name="margin_top" class="form-control" value="5" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Bawah</label>
                        <input type="number" step="0.1" name="margin_bottom" class="form-control" value="5"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Kiri</label>
                        <input type="number" step="0.1" name="margin_left" class="form-control" value="5"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Kanan</label>
                        <input type="number" step="0.1" name="margin_right" class="form-control" value="5"
                            required>
                    </div>
                </div>

                <h3 class="card-title mt-4">Jarak Antar Label (mm)</h3>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Gap Horizontal (X)</label>
                        <input type="number" step="0.1" name="gap_x" class="form-control" value="2" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Gap Vertikal (Y)</label>
                        <input type="number" step="0.1" name="gap_y" class="form-control" value="2" required>
                    </div>
                </div>

                <h3 class="card-title mt-4">Tipografi</h3>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Ukuran Font Dasar (pt)</label>
                        <input type="number" step="1" name="font_size" class="form-control" value="10" required>
                    </div>
                    <div class="col-md-6 pt-4">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <span class="form-check-label">Aktifkan Sekarang (Nonaktifkan yang lain)</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Simpan Layout</button>
            </div>
        </div>
    </form>
@endsection
