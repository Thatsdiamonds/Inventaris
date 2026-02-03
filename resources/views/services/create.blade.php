@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Pencatatan Servis Baru</h1>
        <p class="text-secondary">Pindahkan barang ke status perbaikan</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: var(--spacing-xl); align-items: start;">

        <div class="card">
            <form action="{{ route('items.service.confirm', $item->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Vendor / Teknisi <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="vendor" value="{{ old('vendor') }}" required
                        placeholder="Nama bengkel atau nama teknisi">
                </div>

                <div class="form-group">
                    <label>Tipe Servis <span style="color: var(--color-danger);">*</span></label>
                    <select name="service_type" required>
                        <option value="routine">Routine Maintenance (Rutin & Reset Jadwal)</option>
                        <option value="manual">Manual Service (Perbaikan Insidental)</option>
                    </select>
                    <div class="alert alert-info py-2 mt-2 mb-0"
                        style="font-size: 0.8rem; border: none; background: var(--color-bg-tertiary);">
                        <svg class="icon icon-sm">
                            <use href="#icon-info"></use>
                        </svg>
                        Tipe <strong>Routine</strong> akan memperbarui hitungan jatuh tempo servis berkala berikutnya.
                    </div>
                </div>

                <div class="form-group" style="max-width: 250px;">
                    <label>Tanggal Masuk <span style="color: var(--color-danger);">*</span></label>
                    <input type="date" name="date_in" value="{{ old('date_in', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label>Keterangan / Gejala Kerusakan <span style="color: var(--color-danger);">*</span></label>
                    <textarea name="description" required placeholder="Jelaskan detail kerusakan atau rencana pembersihan..."
                        style="height: 120px;">{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-2 mt-4"
                    style="border-top: 1px solid var(--color-border-light); padding-top: var(--spacing-lg);">
                    <button type="submit" class="btn btn-primary">Lanjut ke Konfirmasi</button>
                    <a href="{{ route('items.index') }}" wire:navigate class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>

        <!-- Info Column -->
        <div class="card" style="background: var(--color-bg-secondary);">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;">Informasi Barang</h3>

            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
                @if ($item->photo_path)
                    <img src="{{ asset('storage/' . $item->photo_path) }}"
                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                @else
                    <div
                        style="width: 60px; height: 60px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                        <svg class="icon icon-lg">
                            <use href="#icon-box"></use>
                        </svg>
                    </div>
                @endif
                <div>
                    <strong style="display: block;">{{ $item->name }}</strong>
                    <code style="font-size: 0.8rem;">{{ $item->uqcode }}</code>
                </div>
            </div>

            <div style="font-size: 0.875rem; color: var(--color-text-secondary);">
                <div class="mb-2"><strong>Lokasi:</strong> {{ $item->location->name }}</div>
                <div class="mb-2"><strong>Kategori:</strong> {{ $item->category->name }}</div>
                <div class="mb-2"><strong>Kondisi Saat Ini:</strong> <span
                        class="badge badge-warning">{{ ucfirst($item->condition) }}</span></div>
            </div>
        </div>
    </div>
@endsection
