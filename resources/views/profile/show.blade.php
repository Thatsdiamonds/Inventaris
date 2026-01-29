@extends('layouts.app')

@section('content')
    <h1>Profil Saya</h1>

    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
        <div style="margin-bottom: 20px;">
            <label style="color: #666; font-size: 0.9em;">Nama Lengkap</label>
            <div style="font-weight: bold; font-size: 1.1em;">{{ $user->name }}</div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="color: #666; font-size: 0.9em;">Username</label>
            <div style="font-weight: bold;">{{ $user->username }}</div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="color: #666; font-size: 0.9em;">Role</label>
            <div style="font-weight: bold;">
                @if ($user->isRoot())
                    <span style="color: #1890ff;">Super Admin (Root)</span>
                @elseif($role)
                    {{ $role->name }}
                @else
                    <span style="color: #ff4d4f;">Tanpa Role</span>
                @endif
            </div>
        </div>

        @if ($user->notes)
            <div style="margin-bottom: 20px;">
                <label style="color: #666; font-size: 0.9em;">Catatan/Alias</label>
                <div>{{ $user->notes }}</div>
            </div>
        @endif

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <h3>Akses Fitur</h3>
        <ul style="padding-left: 20px;">
            @if ($user->isRoot())
                <li>Akses penuh ke semua fitur sistem.</li>
            @elseif($role && $role->permissions)
                @foreach ($role->permissions as $perm)
                    <li>{{ ucwords(str_replace('_', ' ', str_replace('access_', '', $perm))) }}</li>
                @endforeach
            @else
                <li style="color: #ff4d4f;">Tidak ada izin fitur yang diberikan.</li>
            @endif
        </ul>

        <h3>Otoritas Lokasi</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if ($user->isRoot())
                <span style="background: #f0f0f0; padding: 5px 10px; border-radius: 4px;">Semua Lokasi</span>
            @elseif($authorizedLocations->isNotEmpty())
                @foreach ($authorizedLocations as $loc)
                    <span style="background: #f0f0f0; padding: 5px 10px; border-radius: 4px;">{{ $loc->name }}</span>
                @endforeach
            @else
                <span style="color: #ff4d4f;">Tidak ada akses lokasi khusus (Akses Semua atau Terkunci).</span>
            @endif
        </div>
    </div>
@endsection
