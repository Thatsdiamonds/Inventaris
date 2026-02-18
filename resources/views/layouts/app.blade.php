<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventaris' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons.css') }}">

    @livewireStyles

    <style>
        /* Dynamic Layout Variables */
        :root {
            --sidebar-width: 260px;
        }

        .app-wrapper {
            min-height: 100vh;
            background: var(--c-bg-app);
            display: flex;
        }

        .main-container {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.1s ease-out;
            width: calc(100% - var(--sidebar-width));
        }

        /* Page Loader */
        #page-loader {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(2px);
            z-index: 9999;
            display: none;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
        }

        #page-loader.active {
            display: block;
            opacity: 1;
            pointer-events: all;
        }

        .loader-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 2px;
            width: 0;
            background: var(--c-accent);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            transition: width 0.3s ease-out;
        }
    </style>

    <script>
        function initSidebar() {
            const root = document.documentElement;
            const sidebarNav = document.querySelector('.sidebar-nav');
            const resizer = document.getElementById('sidebarResizer');

            // 1. Restore Sidebar Width
            const savedWidth = localStorage.getItem('sidebar-width');
            if (savedWidth) {
                root.style.setProperty('--sidebar-width', savedWidth + 'px');
            }

            // 2. Restore Scroll Position
            if (sidebarNav) {
                const scrollPos = localStorage.getItem('sidebar-scroll');
                if (scrollPos) {
                    sidebarNav.scrollTop = parseInt(scrollPos);
                }

                // Add listener if not already attached
                if (!sidebarNav.dataset.hasScrollListener) {
                    sidebarNav.addEventListener('scroll', () => {
                        localStorage.setItem('sidebar-scroll', sidebarNav.scrollTop);
                    });
                    sidebarNav.dataset.hasScrollListener = 'true';
                }
            }

            // 3. Init Resizer (if exists and not initialized)
            if (resizer && !resizer.dataset.hasResizerListener) {
                let isResizing = false;

                resizer.addEventListener('mousedown', (e) => {
                    isResizing = true;
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isResizing) return;
                    let newWidth = e.clientX;
                    if (newWidth < 200) newWidth = 200;
                    if (newWidth > 450) newWidth = 450;
                    root.style.setProperty('--sidebar-width', newWidth + 'px');
                });

                document.addEventListener('mouseup', () => {
                    if (isResizing) {
                        isResizing = false;
                        document.body.style.cursor = 'default';
                        document.body.style.userSelect = 'auto';
                        const currentWidth = getComputedStyle(root).getPropertyValue('--sidebar-width').replace(
                            'px', '').trim();
                        localStorage.setItem('sidebar-width', currentWidth);
                    }
                });

                resizer.dataset.hasResizerListener = 'true';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
        });

        // Livewire Navigation Events
        document.addEventListener('livewire:navigating', () => {
            const loader = document.getElementById('page-loader');
            const progress = document.querySelector('.loader-progress');
            if (loader) loader.classList.add('active');
            if (progress) progress.style.width = '70%';
        });

        document.addEventListener('livewire:navigated', () => {
            const loader = document.getElementById('page-loader');
            const progress = document.querySelector('.loader-progress');
            if (progress) progress.style.width = '100%';

            setTimeout(() => {
                if (loader) loader.classList.remove('active');
                if (progress) progress.style.width = '0%';
            }, 300);

            // Re-init sidebar on navigation end
            initSidebar();
        });
    </script>
</head>

<body>
    <!-- Icon Sprite -->
    @include('components.icon-sprite')

    <!-- Global Page Loader -->
    <div id="page-loader">
        <div class="loader-progress"></div>
    </div>

    @auth
        <div class="app-wrapper">
            @if (!request()->is('reports/layout/*'))
                <x-sidebar />
                <main class="main-container">
                    @yield('content')
                </main>
            @else
                <main class="main-container" style="margin-left: 0; max-width: 1400px; margin: 0 auto;">
                    @yield('content')
                </main>
            @endif
        </div>
    @else
        <main>
            @yield('content')
        </main>
    @endauth

    @livewireScripts
</body>

</html>
