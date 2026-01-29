<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventaris' }}</title>
    @livewireStyles
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: #f9f9f9;
            color: #333;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-container {
            flex: 1;
            padding: 40px;
            box-sizing: border-box;
            background: #f9f9f9;
            overflow-x: hidden;
        }
    </style>
</head>

<body>
    @auth
        <div class="app-wrapper">
            <x-sidebar />
            <main class="main-container">
                @yield('content')
            </main>
        </div>
    @else
        <main>
            @yield('content')
        </main>
    @endauth
    @livewireScripts
</body>

</html>
