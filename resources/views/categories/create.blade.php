@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Tambah Kategori</h1>
        <p class="text-secondary">Kelompokkan barang agar lebih mudah dikelola</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="mb-0" style="padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nama Kategori <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    placeholder="Contoh: Perangkat Elektronik" required onkeyup="syncCode()">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="auto_code" id="auto_code" value="1" checked
                        onchange="toggleCodeLock()">
                    <span style="font-size: 0.9rem;">Generate kode unik otomatis dari nama</span>
                </label>
            </div>

            <div class="form-group">
                <label>Kode Unik (4-16 karakter) <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="unique_code" id="unique_code" value="{{ old('unique_code') }}" minlength="4"
                    maxlength="16" required placeholder="Elektronik">
                <small class="text-muted">Kode ini digunakan sebagai pengenal dalam sistem barcode/QR.</small>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" placeholder="Penjelasan singkat mengenai kategori ini...">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                <a href="{{ route('categories.index') }}" wire:navigate class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>

    <script>
        function syncCode() {
            const name = document.getElementById('name').value;
            const isAuto = document.getElementById('auto_code').checked;
            if (isAuto) {
                const sanitized = name.replace(/[^A-Za-z0-9\s]/g, '')
                    .replace(/\w+/g, function(w) {
                        return w[0].toUpperCase() + w.slice(1).toLowerCase();
                    })
                    .replace(/\s/g, '');
                document.getElementById('unique_code').value = sanitized;
            }
        }

        function toggleCodeLock() {
            const isAuto = document.getElementById('auto_code').checked;
            const codeInput = document.getElementById('unique_code');
            if (isAuto) {
                codeInput.readOnly = true;
                codeInput.style.background = "var(--color-bg-tertiary)";
                syncCode();
            } else {
                codeInput.readOnly = false;
                codeInput.style.background = "white";
            }
        }

        document.addEventListener('DOMContentLoaded', toggleCodeLock);
    </script>
@endsection
