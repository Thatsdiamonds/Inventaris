@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Laporan Inventaris</h1>
        <p class="text-secondary">Filter dan cetak laporan daftar barang secara spesifik.</p>
    </div>

    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ route('reports.inventory.generate') }}">
            @csrf

            <!-- Scope Section -->
            <div class="form-section mb-4">
                <h3 class="mb-3"
                    style="font-size: 1rem; border-bottom: 1px solid var(--color-border-light); padding-bottom: 0.5rem;">
                    1. Cakupan Laporan
                </h3>

                <div class="form-group mb-4">
                    <label>Pilih Scope Data</label>
                    <select name="scope" id="scope" required class="form-select" onchange="toggleScope(this.value)">
                        <option value="">-- Pilih Cakupan --</option>
                        <option value="lokasi">Berdasarkan Lokasi</option>
                        <option value="kategori">Berdasarkan Kategori</option>
                        <option value="barang">Barang Spesifik</option>
                        <option value="semua">Semua Barang</option>
                    </select>
                </div>

                {{-- SCOPE LOKASI --}}
                <div id="lokasi" class="scope-content"
                    style="display: none; background: var(--color-bg-secondary); padding: 1rem; border-radius: var(--radius-md);">
                    <label class="mb-2 block font-semibold">Pilih Lokasi</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem;">
                        @foreach ($locations as $l)
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="locations[]" value="{{ $l->id }}">
                                <span>{{ $l->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SCOPE KATEGORI --}}
                <div id="kategori" class="scope-content"
                    style="display: none; background: var(--color-bg-secondary); padding: 1rem; border-radius: var(--radius-md);">
                    <label class="mb-2 block font-semibold">Pilih Kategori</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem;">
                        @foreach ($categories as $c)
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="categories[]" value="{{ $c->id }}">
                                <span>{{ $c->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SCOPE BARANG --}}
                <div id="barang" class="scope-content"
                    style="display: none; background: var(--color-bg-secondary); padding: 1rem; border-radius: var(--radius-md);">
                    <label class="mb-2 block font-semibold">Pilih Barang</label>
                    <div class="search-wrapper bg-white">
                        <select name="item_id" class="form-select" style="width: 100%;">
                            <option value="">-- Cari Barang --</option>
                            @foreach ($items as $i)
                                <option value="{{ $i->id }}">{{ $i->name }} ({{ $i->uqcode }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="form-section mb-4">
                <h3 class="mb-3"
                    style="font-size: 1rem; border-bottom: 1px solid var(--color-border-light); padding-bottom: 0.5rem;">
                    2. Filter Tambahan
                </h3>

                <div class="form-group mb-3">
                    <label>Kondisi Barang</label>
                    <select name="condition" class="form-select">
                        <option value="all">Semua Kondisi</option>
                        <option value="Baik">Baik</option>
                        <option value="Rusak">Rusak</option>
                        <option value="Perbaikan">Dalam Perbaikan</option>
                        <option value="Dimusnahkan">Dimusnahkan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Periode Input (Tanggal Dibuat)</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="date" name="from" id="date_from" class="form-input">
                        <span>s/d</span>
                        <input type="date" name="to" id="date_to" class="form-input">
                    </div>
                    <div class="mt-2" style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-ghost btn-xs" onclick="setDateRange(7)">7 Hari
                            Terakhir</button>
                        <button type="button" class="btn btn-ghost btn-xs" onclick="setDateRange(30)">30 Hari
                            Terakhir</button>
                        <button type="button" class="btn btn-ghost btn-xs" onclick="setDateRange(365)">1 Tahun
                            Terakhir</button>
                        <button type="button" class="btn btn-ghost btn-xs" onclick="clearDateRange()">Reset</button>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-5 pt-3"
                style="border-top: 1px solid var(--color-border-light); display: flex; justify-content: space-between; align-items: center;">
                <a href="{{ route('reports.layout.edit', 'inventory') }}" class="btn btn-ghost text-secondary">
                    <svg class="icon icon-sm">
                        <use href="#icon-settings"></use>
                    </svg>
                    Atur Kolom Laporan
                </a>

                <button type="submit" class="btn btn-primary">
                    <svg class="icon icon-sm">
                        <use href="#icon-report"></use>
                    </svg>
                    Generate PDF
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleScope(scope) {
            document.querySelectorAll('.scope-content').forEach(el => el.style.display = 'none');
            if (scope && scope !== 'semua') {
                const target = document.getElementById(scope);
                if (target) target.style.display = 'block';
            }
        }

        function setDateRange(days) {
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - days);

            document.getElementById('date_to').valueAsDate = end;
            document.getElementById('date_from').valueAsDate = start;
        }

        function clearDateRange() {
            document.getElementById('date_to').value = '';
            document.getElementById('date_from').value = '';
        }
    </script>
@endsection
