@extends('layouts.app')

@section('content')
    <h2>Laporan Inventaris</h2>

    <form method="POST" action="{{ route('reports.inventory.generate') }}"
        style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
        @csrf

        <label><strong>Scope Laporan</strong></label>
        <select name="scope" id="scope" required
            style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
            <option value="">-- pilih --</option>
            <option value="lokasi">Lokasi</option>
            <option value="kategori">Kategori</option>
            <option value="barang">Barang</option>
        </select>

        <hr>

        {{-- SCOPE LOKASI --}}
        <div id="lokasi" class="scope" style="margin-bottom: 15px;">
            <label><strong>Lokasi</strong></label><br>
            @foreach ($locations as $l)
                <label style="display: block; margin: 5px 0;">
                    <input type="checkbox" name="locations[]" value="{{ $l->id }}">
                    {{ $l->name }}
                </label>
            @endforeach
        </div>

        {{-- SCOPE KATEGORI --}}
        <div id="kategori" class="scope" style="margin-bottom: 15px;">
            <label><strong>Kategori</strong></label><br>
            @foreach ($categories as $c)
                <label style="display: block; margin: 5px 0;">
                    <input type="checkbox" name="categories[]" value="{{ $c->id }}">
                    {{ $c->name }}
                </label>
            @endforeach
        </div>

        {{-- SCOPE BARANG --}}
        <div id="barang" class="scope" style="margin-bottom: 15px;">
            <label><strong>Barang</strong></label>
            <select name="item_id" style="width: 100%; padding: 8px; margin-top: 5px;">
                <option value="">-- pilih barang --</option>
                @foreach ($items as $i)
                    <option value="{{ $i->id }}">{{ $i->name }}</option>
                @endforeach
            </select>
        </div>

        <hr>

        <label><strong>Kondisi</strong></label>
        <select name="condition" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
            <option value="all">Semua</option>
            <option value="Baik">Baik</option>
            <option value="Rusak">Rusak</option>
            <option value="Perbaikan">Perbaikan</option>
        </select>

        <hr>

        <label><strong>Periode</strong></label>
        <div style="display: flex; gap: 15px; margin-top: 10px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label>Dari</label>
                <input type="date" name="from" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="flex: 1;">
                <label>Sampai</label>
                <input type="date" name="to" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
        </div>

        <hr>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <a href="{{ route('reports.layout.edit', 'inventory') }}"
                style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">
                Atur Konten Laporan Inventaris
            </a>
            <button type="submit"
                style="background: #1890ff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                Generate PDF
            </button>
        </div>
    </form>

    <script>
        document.querySelectorAll('.scope').forEach(e => e.style.display = 'none');

        document.getElementById('scope').addEventListener('change', function() {
            document.querySelectorAll('.scope').forEach(e => e.style.display = 'none');
            if (this.value) {
                document.getElementById(this.value).style.display = 'block';
            }
        });
    </script>
@endsection
