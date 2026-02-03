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
        :root {
            --sidebar-width: 260px;
        }

        .app-wrapper {
            min-height: 100vh;
            background: var(--color-bg-secondary);
        }

        .main-container {
            margin-left: var(--sidebar-width);
            padding: var(--spacing-lg);
            box-sizing: border-box;
            overflow-x: hidden;
            animation: fadeIn 0.3s ease;
            min-height: 100vh;
            transition: margin-left 0.05s linear;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Page Loader Styles */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
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
            height: 3px;
            width: 0;
            background: var(--color-accent);
            box-shadow: 0 0 10px var(--color-accent);
            z-index: 10000;
            transition: width 0.4s ease;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const resizer = document.getElementById('sidebarResizer');
            const root = document.documentElement;
            const sidebar = document.querySelector('.sidebar');

            // Load saved width
            const savedWidth = localStorage.getItem('sidebar-width');
            if (savedWidth) {
                root.style.setProperty('--sidebar-width', savedWidth + 'px');
            }

            if (resizer) {
                let isResizing = false;

                resizer.addEventListener('mousedown', (e) => {
                    isResizing = true;
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isResizing) return;

                    let newWidth = e.clientX;

                    // Min and Max constraints
                    if (newWidth < 200) newWidth = 200;
                    if (newWidth > 450) newWidth = 450;

                    root.style.setProperty('--sidebar-width', newWidth + 'px');
                });

                document.addEventListener('mouseup', () => {
                    if (isResizing) {
                        isResizing = false;
                        document.body.style.cursor = 'default';
                        document.body.style.userSelect = 'auto';

                        // Save width
                        const currentWidth = getComputedStyle(root).getPropertyValue('--sidebar-width')
                            .replace('px', '').trim();
                        localStorage.setItem('sidebar-width', currentWidth);
                    }
                });
            }
        });

        // Livewire Navigation Events for Loader
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
