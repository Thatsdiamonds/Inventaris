@extends('layouts.app')

@section('content')
    <h1>Edit Pengguna: {{ $user->name }}</h1>

    <form action="{{ route('users.update', $user->id) }}" method="POST"
        style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 15px;">
            <label><strong>Nama Lengkap:</strong></label><br>
            <input type="text" name="name" value="{{ $user->name }}" required
                style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Username:</strong></label><br>
            <input type="text" name="username" value="{{ $user->username }}" required
                style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Password Baru (Kosongkan jika tidak diubah):</strong></label><br>
            <input type="password" name="password" style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Konfirmasi Password Baru:</strong></label><br>
            <input type="password" name="password_confirmation" style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Pilih Role:</strong></label><br>
            <select name="role_id" required style="width: 100%; padding: 8px; margin-top: 5px;">
                <option value="">-- Pilih Role --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Catatan/Alias (Opsional):</strong></label><br>
            <textarea name="notes" style="width: 100%; padding: 8px; margin-top: 5px; height: 80px;">{{ $user->notes }}</textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit"
                style="background: #1890ff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Perbarui
                Pengguna</button>
            <a href="{{ route('users.index') }}" style="margin-left: 15px; color: #666; text-decoration: none;">Batal</a>
        </div>
    </form>
@endsection
