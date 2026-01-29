@extends('layouts.app')

@section('content')
    <h1>Tambah Pengguna Baru</h1>

    <form action="{{ route('users.store') }}" method="POST"
        style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
        @csrf

        <div style="margin-bottom: 15px;">
            <label><strong>Nama Lengkap:</strong></label><br>
            <input type="text" name="name" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Username:</strong></label><br>
            <input type="text" name="username" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Password:</strong></label><br>
            <input type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Konfirmasi Password:</strong></label><br>
            <input type="password" name="password_confirmation" required
                style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Pilih Role:</strong></label><br>
            <select name="role_id" required style="width: 100%; padding: 8px; margin-top: 5px;">
                <option value="">-- Pilih Role --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Catatan/Alias (Opsional):</strong></label><br>
            <textarea name="notes" style="width: 100%; padding: 8px; margin-top: 5px; height: 80px;"
                placeholder="Contoh: Staf Admin Kantor Pusat"></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit"
                style="background: #1890ff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Simpan
                Pengguna</button>
            <a href="{{ route('users.index') }}" style="margin-left: 15px; color: #666; text-decoration: none;">Batal</a>
        </div>
    </form>
@endsection
