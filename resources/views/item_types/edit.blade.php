@extends('layouts.app')

@section('content')
    <div class="page-header mb-4 flex-between">
        <div>
            <h1 class="mb-0">Edit Nama Aset</h1>
            <p class="text-secondary">Ubah informasi nama dan kode grup aset</p>
        </div>
        <a href="{{ route('item-types.index') }}" class="btn btn-ghost">Kembali</a>
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
        <form action="{{ route('item-types.update', $itemType->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Aset <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $itemType->name) }}" required
                    onkeyup="syncCode()">
            </div>

            <div class="form-group">
                <label>Kode Prefix (Unik) <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="unique_code" id="unique_code"
                    value="{{ old('unique_code', $itemType->unique_code) }}" required
                    style="font-family: monospace; text-transform: uppercase;"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,''); checkCodeChange()">

                <div id="code_warning" class="alert alert-warning py-3 mt-3 mb-0"
                    style="display:none; font-size:0.875rem; border-left: 4px solid var(--color-warning);">
                    <div style="font-weight: 700; margin-bottom: 0.25rem; color: var(--color-primary);">
                        <svg class="icon icon-sm">
                            <use href="#icon-alert"></use>
                        </svg>
                        Peringatan Perubahan Kode
                    </div>
                    Anda mengubah Kode Prefix. Perubahan ini akan memperbarui <strong>{{ $itemCount }} barang</strong>
                    yang terhubung dalam grup ini secara otomatis.
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Penjelasan singkat mengenai grup aset ini...">{{ old('description', $itemType->description) }}</textarea>
            </div>

            <div class="flex gap-2 mt-4"
                style="border-top: 1px solid var(--color-border-light); padding-top: var(--spacing-lg);">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('item-types.index') }}" wire:navigate class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>

    <script>
        const originalCode = "{{ $itemType->unique_code }}";
        const itemCount = {{ $itemCount }};

        function checkCodeChange() {
            const currentCode = document.getElementById('unique_code').value;
            const warning = document.getElementById('code_warning');
            if (itemCount > 0 && currentCode !== originalCode && currentCode.length > 0) {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }

        function syncCode() {
            // No auto-sync for edit usually, but keeping it simple
        }

        document.addEventListener('DOMContentLoaded', checkCodeChange);
    </script>
@endsection
