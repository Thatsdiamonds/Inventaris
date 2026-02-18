@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Edit Role</h1>
        <p class="text-secondary">Perbarui informasi dan izin akses peran</p>
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
        <form action="{{ route('roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-4">
                <label>Nama Role <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required>
            </div>

            <div class="form-group mb-6">
                <label>Catatan/Penjelasan</label>
                <textarea name="notes" rows="2">{{ old('notes', $role->notes) }}</textarea>
            </div>

            <div class="grid-2 gap-6">
                <!-- Permissions Section -->
                <div>
                    <h3 class="card-title mb-3 border-bottom pb-2">Izin Akses Fitur</h3>
                    <div class="bg-secondary p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                        @foreach ($availablePermissions as $key => $label)
                            <label class="flex-start gap-2 mb-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                    class="form-checkbox" {{ in_array($key, $role->permissions ?? []) ? 'checked' : '' }}>
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Locations Section -->
                <div>
                    <h3 class="card-title mb-3 border-bottom pb-2">Batasan Lokasi</h3>
                    <div class="bg-secondary p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                        @foreach ($locations as $loc)
                            <label class="flex-start gap-2 mb-2 cursor-pointer">
                                <input type="checkbox" name="location_ids[]" value="{{ $loc->id }}"
                                    class="form-checkbox" {{ in_array($loc->id, $selectedLocations) ? 'checked' : '' }}>
                                <span class="text-sm">{{ $loc->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-2 text-xs text-muted">
                        <svg class="icon icon-xs inline-block">
                            <use href="#icon-info"></use>
                        </svg>
                        Jika tidak ada lokasi yang dipilih, role ini akan memiliki akses ke <strong>Semua Lokasi
                            (Global)</strong>.
                    </div>
                </div>
            </div>

            <div class="flex-end gap-2 mt-6 pt-4 border-top">
                <a href="{{ route('roles.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
