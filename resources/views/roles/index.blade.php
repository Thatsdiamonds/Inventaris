@extends('layouts.app')

@section('content')
    <h1>Manajemen Roles</h1>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('roles.create') }}" wire:navigate
            style="background: #1890ff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">Tambah
            Role Baru</a>
    </div>

    @if (session('success'))
        <div style="color: green; background: #e6ffed; padding: 10px; border: 1px solid green; margin-bottom: 15px;">
            {{ session('success') }}</div>
    @endif

    <table border="1" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead>
            <tr style="background: #fafafa;">
                <th>Nama Role</th>
                <th>Catatan</th>
                <th>Izin</th>
                <th>Lokasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td><strong>{{ $role->name }}</strong></td>
                    <td style="color: #666; font-size: 0.9em;">{{ $role->notes ?? '-' }}</td>
                    <td>
                        <ul style="margin: 0; padding-left: 15px;">
                            @foreach ($role->permissions ?? [] as $perm)
                                <li><small>{{ str_replace('_', ' ', ucfirst($perm)) }}</small></li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        @if ($role->locations->count() > 0)
                            @foreach ($role->locations as $loc)
                                <span
                                    style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-size: 0.85em;">{{ $loc->name }}</span>
                            @endforeach
                        @else
                            <span style="color: #999; font-style: italic;">Semua Lokasi</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('roles.edit', $role->id) }}" wire:navigate>Edit</a> |
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus role ini?')"
                                style="background: none; border: none; color: red; cursor: pointer; padding: 0;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
