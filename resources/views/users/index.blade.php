@extends('layouts.app')

@section('content')
    <h1>Manajemen Pengguna</h1>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('users.create') }}" wire:navigate
            style="background: #1890ff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">Tambah
            Pengguna Baru</a>
    </div>

    @if (session('success'))
        <div style="color: green; background: #e6ffed; padding: 10px; border: 1px solid green; margin-bottom: 15px;">
            {{ session('success') }}</div>
    @endif

    <table border="1" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead>
            <tr style="background: #fafafa;">
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Catatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>
                        {{ $user->name }}
                        @if ($user->isRoot())
                            <span
                                style="background: #000; color: #fff; padding: 2px 5px; border-radius: 3px; font-size: 0.75em; vertical-align: middle;">ROOT</span>
                        @endif
                    </td>
                    <td>{{ $user->username }}</td>
                    <td>
                        @if ($user->isRoot())
                            <span style="color: #666; font-style: italic;">Super Admin</span>
                        @elseif($user->assignedRole)
                            <strong>{{ $user->assignedRole->name }}</strong>
                        @else
                            <span style="color: #ff4d4f;">Tanpa Role</span>
                        @endif
                    </td>
                    <td style="color: #666; font-size: 0.9em;">{{ $user->notes ?? '-' }}</td>
                    <td>
                        @if (!$user->isRoot())
                            <a href="{{ route('users.edit', $user->id) }}" wire:navigate>Edit</a> |
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus pengguna ini?')"
                                    style="background: none; border: none; color: red; cursor: pointer; padding: 0;">Hapus</button>
                            </form>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
