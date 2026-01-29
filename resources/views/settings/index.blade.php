@extends('layouts.app')

@section('content')
    <h1>Settings</h1>

    @if (session('success'))
        <div style="background: #e6ffed; border: 1px solid #52c41a; color: #52c41a; padding: 10px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST"
        style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
        @csrf

        <div style="margin-bottom: 20px;">
            <label><strong>Nama Organisasi / Gereja:</strong></label><br>
            <input type="text" name="nama_gereja" value="{{ $settings->nama_gereja ?? 'Inventaris Management' }}"
                style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Maintenance Warning Threshold (Days):</strong></label><br>
            <input type="number" name="maintenance_threshold" value="{{ $settings->maintenance_threshold ?? 30 }}"
                style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
            <small style="color: #666;">Tentukan berapa hari sebelumnya barang akan muncul di tab "Akan Datang".</small>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Default Pagination:</strong></label><br>
            <input type="number" name="default_pagination" value="{{ $settings->default_pagination ?? 15 }}"
                style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
            <small style="color: #666;">Jumlah item per halaman yang ditampilkan secara default.</small>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>QR Preferences:</strong></label><br>
            <label style="display: block; margin-top: 5px;">
                <input type="checkbox" name="auto_download_after_add" value="1"
                    {{ $settings->auto_download_after_add ?? true ? 'checked' : '' }}>
                Otomatis unduh QR setelah tambah item
            </label>
            <label style="display: block; margin-top: 5px;">
                <input type="checkbox" name="auto_download_after_edit" value="1"
                    {{ $settings->auto_download_after_edit ?? false ? 'checked' : '' }}>
                Otomatis unduh QR setelah edit item
            </label>
        </div>

        <button type="submit"
            style="background: #1890ff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Save
            Settings</button>
    </form>

    <br>
    <a href="{{ url('/') }}" wire:navigate>Back to Dashboard</a>
@endsection
