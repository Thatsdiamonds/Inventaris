@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Tambah Pengguna Baru</h1>
        <p class="text-secondary">Buat akun pengguna baru dan tetapkan peran</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="mb-0 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <h3 class="card-title mb-4">Informasi Akun</h3>

            <div class="grid-2 gap-4 mb-4">
                <div class="form-group">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: John Doe">
                </div>

                <div class="form-group">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        placeholder="Contoh: johndoe">
                </div>
            </div>

            <div class="grid-2 gap-4 mb-4">
                <div class="form-group">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" required placeholder="Min. 8 karakter">
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi password">
                </div>
            </div>

            <h3 class="card-title mb-4 mt-2">Akses & Peran</h3>

            <div class="form-group mb-4">
                <label>Peran (Role) <span class="text-danger">*</span></label>
                <select name="role_id" required>
                    <option value="">-- Pilih Peran --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Peran menentukan hak akses pengguna dalam sistem.</small>
            </div>

            <div class="form-group mb-6">
                <label>Catatan (Opsional)</label>
                <textarea name="notes" rows="3" placeholder="Tambahkan catatan tentang pengguna ini...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            </div>
        </form>
    </div>
@endsection
