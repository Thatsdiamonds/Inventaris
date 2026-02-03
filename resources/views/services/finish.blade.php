@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Penyelesaian Servis</h1>
        <p class="text-secondary">Catat hasil perbaikan dan biaya akhir</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: var(--spacing-xl); align-items: start;">

        <div class="card">
            <form action="{{ route('services.finish', $service->id) }}" method="POST">
                @csrf

                <div class="form-group" style="max-width: 250px;">
                    <label>Tanggal Selesai <span style="color: var(--color-danger);">*</span></label>
                    <input type="date" name="date_out" value="{{ old('date_out', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label>Biaya Aktual (IDR)</label>
                    <div style="position: relative;">
                        <span
                            style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--color-text-secondary);">Rp</span>
                        <input type="number" name="cost" value="{{ old('cost', $service->estimated_cost) }}"
                            placeholder="0" style="padding-left: 3rem;">
                    </div>
                    <small class="text-muted">Biaya nyata yang dibayarkan ke vendor.</small>
                </div>

                <div class="form-group">
                    <label>Kondisi Barang Setelah Servis <span style="color: var(--color-danger);">*</span></label>
                    <select name="condition_after" required>
                        <option value="baik" {{ old('condition_after') == 'baik' ? 'selected' : '' }}>Baik (Siap digunakan
                            kembali)</option>
                        <option value="rusak" {{ old('condition_after') == 'rusak' ? 'selected' : '' }}>Rusak (Masih
                            bermasalah)</option>
                        <option value="perbaikan" {{ old('condition_after') == 'perbaikan' ? 'selected' : '' }}>Perbaikan
                            (Lanjut servis tahap berikutnya)</option>
                    </select>
                </div>

                <div class="flex gap-2 mt-4"
                    style="border-top: 1px solid var(--color-border-light); padding-top: var(--spacing-lg);">
                    <button type="submit" class="btn btn-success">
                        <svg class="icon icon-sm">
                            <use href="#icon-check"></use>
                        </svg>
                        Selesaikan Servis
                    </button>
                    <form action="{{ route('services.fails', $service->id) }}" method="POST"
                        onsubmit="return confirm('Tandai servis ini sebagai gagal?')" style="display: inline;">
                        @csrf
                        <input type="hidden" name="date_out" value="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-ghost" style="color: var(--color-danger);">Gagal
                            Servis</button>
                    </form>
                    <a href="{{ route('services.index') }}" wire:navigate class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>

        <!-- Info Column -->
        <div class="card" style="background: var(--color-bg-secondary);">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;">Detail Servis</h3>

            <div style="font-size: 0.875rem; color: var(--color-text-secondary);">
                <div class="mb-3">
                    <strong>Barang:</strong>
                    <div style="color: var(--color-text); font-weight: 700;">{{ $service->item->name }}</div>
                    <code>{{ $service->item->uqcode }}</code>
                </div>
                <div class="mb-3">
                    <strong>Vendor / Teknisi:</strong>
                    <div style="color: var(--color-text);">{{ $service->vendor }}</div>
                </div>
                <div class="mb-3">
                    <strong>Mulai Servis:</strong>
                    <div style="color: var(--color-text);">{{ $service->date_in->format('d M Y') }}</div>
                </div>
                <div class="mb-3">
                    <strong>Keterangan Masuk:</strong>
                    <div
                        style="font-style: italic; background: white; padding: 0.5rem; border-radius: 4px; border: 1px solid var(--color-border-light); margin-top: 0.25rem;">
                        "{{ $service->description }}"
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
