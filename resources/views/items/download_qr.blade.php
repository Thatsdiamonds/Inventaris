<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Mengunduh...</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #f4f7f6;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        h2 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 1.25rem;
        }

        p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Toast Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-left: 5px solid #27ae60;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(120%);
            transition: transform 0.3s ease;
            margin-bottom: 10px;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-icon {
            color: #27ae60;
            font-weight: bold;
        }

        .toast-message {
            font-size: 0.875rem;
            color: #1e293b;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="toast-container" id="toastContainer"></div>

    <div class="box">
        <div class="loader"></div>
        <h2>{{ $title ?? 'Memproses Unduhan' }}</h2>
        <p>{{ $message ?? 'File sedang disiapkan, unduhan akan dimulai otomatis.' }}</p>

        <form id="download_form" action="{{ $downloadUrl }}" method="{{ $method ?? 'GET' }}" style="display: none;">
            @csrf
            <input type="hidden" name="download_token" id="download_token">
            @if (isset($params))
                @foreach ($params as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
            @endif
        </form>
    </div>

    <script>
        function showToast(message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-icon">✓</div>
                <div class="toast-message">${message}</div>
            `;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function expireCookie(name) {
            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }

        window.onload = function() {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, "{{ $redirectUrl ?? route('items.index') }}");
            }

            @if (session('success'))
                showToast("{{ session('success') }}");
            @endif

            const token = "dt_" + Date.now();
            document.getElementById('download_token').value = token;

            // Logika Deteksi Single File (Fast Path)
            // Jika item_ids hanya 1 dan format adalah img/default, kita anggap instan
            const itemIds = @json($params['item_ids'] ?? []);
            const format = "{{ $params['format'] ?? '' }}";
            const isSingleFast = (itemIds.length === 1 && (format === 'img' || format === ''));

            setTimeout(() => {
                document.getElementById('download_form').submit();

                if (isSingleFast) {
                    // FAST PATH: Untuk file tunggal, langsung redirect setelah 1.2 detik
                    setTimeout(() => {
                        window.location.replace("{{ $redirectUrl ?? route('items.index') }}");
                    }, 1200);
                } else {
                    // HEAVY PATH: Gunakan polling cookie untuk sinkronisasi
                    let attempts = 0;
                    const checkInterval = setInterval(() => {
                        const cookieValue = getCookie('download_status');
                        attempts++;

                        if (cookieValue === token) {
                            clearInterval(checkInterval);
                            expireCookie('download_status');
                            finishDownload();
                        }

                        // Fallback: Jika 15 detik (30 attempts) tidak ada cookie, paksa redirect
                        if (attempts > 30) {
                            clearInterval(checkInterval);
                            finishDownload();
                        }
                    }, 500);
                }
            }, 600);
        };

        function finishDownload() {
            document.querySelector('h2').innerText = "Selesai!";
            document.querySelector('p').innerText = "File telah disiapkan. Mengalihkan...";
            document.querySelector('.loader').style.display = 'none';

            setTimeout(() => {
                window.location.replace("{{ $redirectUrl ?? route('items.index') }}");
            }, 1000);
        }
    </script>
</body>

</html>
