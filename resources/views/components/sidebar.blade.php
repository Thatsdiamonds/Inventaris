<div class="sidebar"
    style="width: fit-content; min-width: 220px; background: #fff; border-right: 1px solid #eee; padding: 25px; box-sizing: border-box; flex-shrink: 0; display: flex; flex-direction: column;">
    <div class="brand"
        style="font-size: 1.25em; font-weight: bold; margin-bottom: 35px; color: #1890ff; white-space: nowrap;">
        @php
            $setting = \App\Models\Setting::first();
            $appName = 'Inventaris ' . $setting->nama_gereja ?? 'Inventaris Management';
        @endphp
        {{ $appName }}
    </div>

    <nav style="flex-grow: 1;">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li class="nav-item" style="margin-bottom: 8px;">
                <a href="{{ url('/') }}" wire:navigate
                    style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('/') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Dashboard</a>
            </li>

            @if (auth()->user()->hasPermission('access_items'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('items.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('items*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Barang</a>
                </li>
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('qr.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('qr*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Generate
                        QR</a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_services'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('services.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('services*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Servis</a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_categories'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('categories.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('categories*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Kategori</a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_locations'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('locations.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('locations*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Lokasi</a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_reports'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('reports.menu') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('reports*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Laporan</a>
                </li>
            @endif

            <hr style="border: 0; border-top: 1px solid #f0f0f0; margin: 20px 0;">

            @if (auth()->user()->hasPermission('access_users'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('users.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('users*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Pengguna</a>
                </li>
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('roles.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('roles*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Roles</a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_settings'))
                <li class="nav-item" style="margin-bottom: 8px;">
                    <a href="{{ route('settings.index') }}" wire:navigate
                        style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('settings*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Pengaturan</a>
                </li>
            @endif

            <li class="nav-item" style="margin-bottom: 8px;">
                <a href="{{ route('profile.show') }}" wire:navigate
                    style="display: block; padding: 12px 15px; text-decoration: none; color: #333; border-radius: 6px; white-space: nowrap; {{ request()->is('profile*') ? 'background: #e6f7ff; color: #1890ff; font-weight: bold;' : '' }}">Profil
                    Saya</a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer" style="padding-top: 20px;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                style="width: 100%; text-align: left; padding: 12px 15px; background: none; border: none; color: #ff4d4f; cursor: pointer; font-size: 1em; font-weight: bold; border-radius: 6px; transition: background 0.3s;"
                onmouseover="this.style.background='#fff1f0'" onmouseout="this.style.background='none'">Logout</button>
        </form>
    </div>
</div>
