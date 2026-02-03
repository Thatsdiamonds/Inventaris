<aside class="sidebar">
    @php
        $setting = \Illuminate\Support\Facades\Cache::rememberForever('church_settings', function () {
            return \App\Models\Setting::first();
        });
        $appName = $setting->nama_gereja ?? 'Inventaris';
    @endphp

    <div class="sidebar-brand-wrapper">
        @if ($setting && $setting->church_photo_path)
            <div class="sidebar-photo">
                <img src="{{ asset('storage/' . $setting->church_photo_path) }}" alt="{{ $appName }}" loading="lazy">
            </div>
        @endif
        <div class="sidebar-brand {{ !($setting && $setting->church_photo_path) ? 'no-photo' : '' }}">
            <h2 class="brand-title">{{ substr($appName, 0, 25) }}</h2>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-section-title">Menu Utama</li>
            <li class="nav-item">
                <a href="{{ url('/') }}" wire:navigate class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                    title="Dashboard">
                    <svg class="icon">
                        <use href="#icon-dashboard"></use>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>

            @if (auth()->user()->hasPermission('access_items'))
                <li class="nav-item">
                    <a href="{{ route('items.index') }}" wire:navigate
                        class="nav-link {{ request()->is('items*') ? 'active' : '' }}" title="Daftar Barang">
                        <svg class="icon">
                            <use href="#icon-box"></use>
                        </svg>
                        <span>Daftar Barang</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_services'))
                <li class="nav-item">
                    <a href="{{ route('services.index') }}" wire:navigate
                        class="nav-link {{ request()->is('services*') ? 'active' : '' }}" title="Manajemen Servis">
                        <svg class="icon">
                            <use href="#icon-tool"></use>
                        </svg>
                        <span>Manajemen Servis</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_items'))
                <li class="nav-item">
                    <a href="{{ route('qr.index') }}" wire:navigate
                        class="nav-link {{ request()->is('qr*') ? 'active' : '' }}" title="Cetak QR Code">
                        <svg class="icon">
                            <use href="#icon-qr"></use>
                        </svg>
                        <span>Cetak QR Code</span>
                    </a>
                </li>
            @endif

            <li class="nav-section-title">Data Master</li>
            @if (auth()->user()->hasPermission('access_categories'))
                <li class="nav-item">
                    <a href="{{ route('categories.index') }}" wire:navigate
                        class="nav-link {{ request()->is('categories*') ? 'active' : '' }}" title="Kategori Barang">
                        <svg class="icon">
                            <use href="#icon-tag"></use>
                        </svg>
                        <span>Kategori Barang</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_locations'))
                <li class="nav-item">
                    <a href="{{ route('locations.index') }}" wire:navigate
                        class="nav-link {{ request()->is('locations*') ? 'active' : '' }}" title="Lokasi Inventaris">
                        <svg class="icon">
                            <use href="#icon-location"></use>
                        </svg>
                        <span>Lokasi Inventaris</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_reports'))
                <li class="nav-item">
                    <a href="{{ route('reports.menu') }}" wire:navigate
                        class="nav-link {{ request()->is('reports*') ? 'active' : '' }}" title="Laporan Aset">
                        <svg class="icon">
                            <use href="#icon-report"></use>
                        </svg>
                        <span>Laporan Aset</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_users') || auth()->user()->hasPermission('access_settings'))
                <li class="nav-section-title">Sistem</li>
                <li class="nav-item dropdown-toggle">
                    <div class="nav-link dropdown-trigger {{ request()->is('users*', 'roles*', 'settings*') ? 'active' : '' }}"
                        onclick="this.parentElement.classList.toggle('open')">
                        <svg class="icon">
                            <use href="#icon-settings"></use>
                        </svg>
                        <span>Manajemen Sistem</span>
                        <svg class="icon-sm arrow">
                            <use href="#icon-chevron-down"></use>
                        </svg>
                    </div>
                    <ul class="dropdown-menu">
                        @if (auth()->user()->hasPermission('access_users'))
                            <li>
                                <a href="{{ route('users.index') }}" wire:navigate
                                    class="{{ request()->is('users*') ? 'active' : '' }}">
                                    Kelola Pengguna
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('roles.index') }}" wire:navigate
                                    class="{{ request()->is('roles*') ? 'active' : '' }}">
                                    Peran & Izin
                                </a>
                            </li>
                        @endif
                        @if (auth()->user()->hasPermission('access_settings'))
                            <li>
                                <a href="{{ route('settings.index') }}" wire:navigate
                                    class="{{ request()->is('settings*') ? 'active' : '' }}">
                                    Pengaturan Dasar
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info mb-2 px-2 py-1 flex items-center gap-3">
            <div class="user-avatar">
                <svg class="icon-md">
                    <use href="#icon-user"></use>
                </svg>
            </div>
            <div class="user-details overflow-hidden">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->assignedRole->name ?? 'User' }}</div>
            </div>
        </div>

        <div class="footer-actions flex gap-1">
            <a href="{{ route('profile.show') }}" wire:navigate class="nav-link flex-1" title="Profil"
                style="justify-content: center; padding: 8px;">
                <svg class="icon-sm">
                    <use href="#icon-user"></use>
                </svg>
                <span class="ml-1" style="font-size: 0.75rem;">Profil</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="nav-link logout-btn w-full" title="Keluar"
                    style="justify-content: center; padding: 8px; border: none; background: transparent; cursor: pointer;">
                    <svg class="icon-sm">
                        <use href="#icon-logout"></use>
                    </svg>
                    <span class="ml-1" style="font-size: 0.75rem;">Keluar</span>
                </button>
            </form>
        </div>
    </div>

    <div class="sidebar-resizer" id="sidebarResizer"></div>
</aside>

<style>
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width, 260px);
        background: #fbfcfd;
        border-right: 1px solid var(--color-border-light);
        display: flex;
        flex-direction: column;
        z-index: 100;
        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.03);
        transition: width 0.05s linear;
        user-select: none;
    }

    .sidebar-brand-wrapper {
        border-bottom: 1px solid var(--color-border-light);
        flex-shrink: 0;
    }

    .sidebar-photo {
        width: 100%;
        height: 100px;
        overflow: hidden;
    }

    .sidebar-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sidebar-brand {
        padding: var(--spacing-md) var(--spacing-lg);
    }

    .sidebar-brand.no-photo {
        padding: var(--spacing-lg);
    }

    .brand-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--color-primary);
        margin: 0;
        letter-spacing: -0.2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }

    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: var(--spacing-md) 0;
    }

    /* Dropdown CSS */
    .dropdown-toggle .dropdown-menu {
        display: none;
        list-style: none;
        padding: 0 0 0 calc(var(--spacing-lg) + 12px);
        margin: 4px 0;
    }

    .dropdown-toggle.open .dropdown-menu {
        display: block;
    }

    .dropdown-toggle.open .arrow {
        transform: rotate(180deg);
    }

    .dropdown-trigger {
        cursor: pointer;
    }

    .dropdown-trigger .arrow {
        margin-left: auto;
        transition: transform 0.2s;
    }

    .dropdown-menu li a {
        display: block;
        padding: 8px var(--spacing-md);
        color: var(--color-text-secondary);
        text-decoration: none;
        font-size: 0.8rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s;
    }

    .dropdown-menu li a:hover {
        background: rgba(52, 152, 219, 0.05);
        color: var(--color-accent);
    }

    .dropdown-menu li a.active {
        color: var(--color-accent);
        font-weight: 600;
    }

    .nav-list {
        list-style: none;
        padding: 0 var(--spacing-md);
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .nav-item {
        margin: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: 0.65rem var(--spacing-md);
        color: var(--color-text);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all var(--transition-fast);
        border-radius: var(--radius-md);
        white-space: nowrap;
    }

    .nav-link:hover {
        background: rgba(52, 152, 219, 0.05);
        color: var(--color-accent);
        transform: translateX(4px);
    }

    .nav-link:active {
        transform: scale(0.96);
    }

    .nav-link.active {
        background: var(--color-accent);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
    }

    .nav-link.active .icon {
        color: white;
    }

    .nav-link .icon {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
    }

    .nav-section-title {
        padding: var(--spacing-lg) var(--spacing-md) var(--spacing-xs);
        font-size: 0.61rem;
        font-weight: 800;
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .sidebar-footer {
        padding: var(--spacing-md);
        border-top: 1px solid var(--color-border-light);
        flex-shrink: 0;
        background: #f8f9fa;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background: var(--color-accent);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .user-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 0.7rem;
        color: var(--color-text-muted);
    }

    .footer-actions {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    .logout-btn {
        color: var(--color-danger) !important;
    }

    .logout-btn:hover {
        background: rgba(231, 76, 60, 0.08) !important;
    }

    .sidebar-resizer {
        position: absolute;
        right: -2px;
        top: 0;
        bottom: 0;
        width: 6px;
        cursor: col-resize;
        transition: background 0.2s;
        z-index: 101;
    }

    .sidebar-resizer:hover,
    .sidebar-resizer:active {
        background: var(--color-accent);
    }

    /* Scrollbar */
    .sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: var(--color-border);
        border-radius: 2px;
    }
</style>
