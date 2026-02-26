<aside class="sidebar">
    @php
        $setting = \Illuminate\Support\Facades\Cache::rememberForever('church_settings', function () {
            return \App\Models\Setting::first();
        });
        $appName = $setting->nama_gereja ?? 'Inventaris';
    @endphp

    <div class="sidebar-header">
        @if ($setting && $setting->church_photo_path)
            <div class="sidebar-logo">
                <img src="{{ asset('storage/' . $setting->church_photo_path) }}" alt="{{ $appName }}">
            </div>
        @endif
        <div class="sidebar-brand {{ !($setting && $setting->church_photo_path) ? 'no-photo' : '' }}">
            <h2 class="brand-text">{{ $appName }}</h2>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-section">NAVIGASI</li>
            <li class="nav-item">
                <a href="{{ url('/') }}" wire:navigate class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <svg class="icon icon-sm">
                        <use href="#icon-dashboard"></use>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>

            @if (auth()->user()->hasPermission('access_items'))
                <li class="nav-item">
                    <a href="{{ route('items.index') }}" wire:navigate
                        class="nav-link {{ request()->is('items*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-box"></use>
                        </svg>
                        <span>Aset & Barang</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_services'))
                <li class="nav-item">
                    <a href="{{ route('services.index') }}" wire:navigate
                        class="nav-link {{ request()->is('services*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-tool"></use>
                        </svg>
                        <span>Servis & Pemeliharaan</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_items'))
                <li class="nav-item">
                    <a href="{{ route('qr.index') }}" wire:navigate
                        class="nav-link {{ request()->is('qr*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-qr"></use>
                        </svg>
                        <span>Cetak Label QR</span>
                    </a>
                </li>
            @endif

            <li class="nav-section">MASTER DATA</li>
            @if (auth()->user()->hasPermission('access_items'))
                <li class="nav-item">
                    <a href="{{ route('item-types.index') }}" wire:navigate
                        class="nav-link {{ request()->is('item-types*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-tag"></use>
                        </svg>
                        <span>Katalog Nama Barang</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_categories'))
                <li class="nav-item">
                    <a href="{{ route('categories.index') }}" wire:navigate
                        class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-tag"></use>
                        </svg>
                        <span>Kategori Aset</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_locations'))
                <li class="nav-item">
                    <a href="{{ route('locations.index') }}" wire:navigate
                        class="nav-link {{ request()->is('locations*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-location"></use>
                        </svg>
                        <span>Lokasi & Ruangan</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_reports'))
                <li class="nav-item">
                    <a href="{{ route('reports.menu') }}" wire:navigate
                        class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                        <svg class="icon icon-sm">
                            <use href="#icon-report"></use>
                        </svg>
                        <span>Laporan Lengkap</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('access_users') || auth()->user()->hasPermission('access_settings'))
                <li class="nav-section">SISTEM</li>

                @if (auth()->user()->hasPermission('access_users'))
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" wire:navigate
                            class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                            <svg class="icon icon-sm">
                                <use href="#icon-users"></use>
                            </svg>
                            <span>Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}" wire:navigate
                            class="nav-link {{ request()->is('roles*') ? 'active' : '' }}">
                            <svg class="icon icon-sm">
                                <use href="#icon-shield"></use>
                            </svg>
                            <span>Peran & Izin</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('access_settings'))
                    <li class="nav-item">
                        <a href="{{ route('settings.index') }}" wire:navigate
                            class="nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                            <svg class="icon icon-sm">
                                <use href="#icon-settings"></use>
                            </svg>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="name">{{ auth()->user()->name }}</div>
            <div class="role">{{ auth()->user()->assignedRole->name ?? 'Staff' }}</div>
        </div>
        <div class="footer-actions">
            <a href="{{ route('profile.show') }}" title="Profil Saya" class="action-btn">
                <svg class="icon icon-sm">
                    <use href="#icon-user"></use>
                </svg>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" title="Keluar" class="action-btn text-danger">
                    <svg class="icon icon-sm">
                        <use href="#icon-logout"></use>
                    </svg>
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
        background: var(--c-bg-card);
        border-right: 1px solid var(--c-border);
        display: flex;
        flex-direction: column;
        z-index: 50;
        transition: width 0.1s ease-out;
    }

    .sidebar-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid var(--c-border);
    }

    .sidebar-logo img {
        height: 40px;
        object-fit: contain;
        margin-bottom: 0.5rem;
    }

    .brand-text {
        font-size: 1rem;
        font-weight: 700;
        color: var(--c-primary);
        margin: 0;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;      /* maksimal 2 baris */
        -webkit-box-orient: vertical;
    }

    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 0;
    }

    .nav-list {
        list-style: none;
        padding: 0 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .nav-section {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--c-text-tertiary);
        margin: 1.5rem 0 0.5rem 0.75rem;
        letter-spacing: 0.05em;
    }

    .nav-section:first-child {
        margin-top: 0.5rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.75rem;
        color: var(--c-text-secondary);
        font-weight: 500;
        font-size: 0.875rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
    }

    .nav-link:hover {
        background: var(--c-bg-app);
        color: var(--c-primary);
    }

    .nav-link.active {
        background: var(--c-accent-subtle);
        color: var(--c-accent);
    }

    .nav-link svg {
        flex-shrink: 0;
        opacity: 0.7;
    }

    .nav-link.active svg {
        opacity: 1;
    }

    .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid var(--c-border);
        background: var(--c-bg-app);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .avatar {
        width: 32px;
        height: 32px;
        background: var(--c-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        overflow: hidden;
    }

    .user-info .name {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--c-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-info .role {
        font-size: 0.7rem;
        color: var(--c-text-secondary);
    }

    .footer-actions {
        display: flex;
        gap: 0.25rem;
    }

    .action-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        color: var(--c-text-secondary);
        border: none;
        background: transparent;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--c-primary);
    }

    .action-btn.text-danger:hover {
        background: var(--c-danger-bg);
        color: var(--c-danger);
    }

    .sidebar-resizer {
        position: absolute;
        right: -3px;
        top: 0;
        bottom: 0;
        width: 6px;
        cursor: col-resize;
        z-index: 100;
    }

    .sidebar-resizer:hover {
        background: var(--c-accent);
        opacity: 0.5;
    }
</style>
