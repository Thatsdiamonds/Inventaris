@extends('layouts.app')

@section('content')
    <div class="page-header mb-4 flex-between">
        <div>
            <h1 class="mb-0">Tambah Tipe Barang</h1>
            <p class="text-secondary">Buat standarisasi nama barang baru</p>
        </div>
        <a href="{{ route('item-types.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('item-types.store') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label>Nama Barang <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required placeholder="Contoh: Laptop Dell XPS 15">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label>Kode Unik (Name Code) <span class="text-danger">*</span></label>
                <input type="text" name="unique_code" class="form-control @error('unique_code') is-invalid @enderror"
                    value="{{ old('unique_code') }}" required placeholder="Contoh: DELLXPS15">
                <small class="text-muted">Akan digunakan dalam UQ Code. Gunakan huruf dan angka saja, tanpa spasi.</small>
                @error('unique_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-4">
                <label>Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>

            <div class="flex-end gap-2">
                <a href="{{ route('item-types.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
