@extends('layouts.app')

@section('content')
    <h1>Tambah Role Baru</h1>

    <form action="{{ route('roles.store') }}" method="POST"
        style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
        @csrf

        <div style="margin-bottom: 20px;">
            <label><strong>Nama Role:</strong></label><br>
            <input type="text" name="name" required placeholder="Contoh: Admin Lokasi Kantor"
                style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Catatan/Penjelasan Role:</strong></label><br>
            <textarea name="notes" style="width: 100%; padding: 8px; margin-top: 5px; height: 60px;"
                placeholder="Digunakan untuk staf admin di kantor cabang..."></textarea>
        </div>

        <div style="display: flex; gap: 40px;">
            <div style="flex: 1;">
                <label><strong>Izin Akses Fitur:</strong></label><br>
                <div
                    style="background: #fafafa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    @foreach ($availablePermissions as $key => $label)
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"> {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="flex: 1;">
                <label><strong>Batasan Lokasi:</strong></label><br>
                <div
                    style="background: #fafafa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-top: 10px; max-height: 300px; overflow-y: auto;">
                    @foreach ($locations as $loc)
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="checkbox" name="location_ids[]" value="{{ $loc->id }}"> {{ $loc->name }}
                        </label>
                    @endforeach
                </div>
                <small style="color: #666;">Jika kosong, maka role ini dapat mengakses <strong>Semua
                        Lokasi</strong>.</small>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <button type="submit"
                style="background: #1890ff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Simpan
                Role</button>
            <a href="{{ route('roles.index') }}" style="margin-left: 15px; color: #666; text-decoration: none;">Batal</a>
        </div>
    </form>
@endsection
