<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Menyiapkan Laporan' }}</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #f8fafc;
            color: #1e293b;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 450px;
            width: 90%;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 24px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>{{ $title ?? 'Memproses Laporan' }}</h1>
        <p>{{ $message ?? 'Laporan Anda sedang dibuat. Unduhan akan dimulai secara otomatis dalam beberapa detik.' }}
        </p>

        <iframe name="downloadFrame" style="display:none;"></iframe>

    <form id="downloadForm"
      action="{{ $downloadUrl }}"
      method="GET"
      target="downloadFrame"
      style="display:none;">

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
    window.onload = function() {

    setTimeout(() => {
        document.getElementById('downloadForm').submit();
    }, 800);

    setTimeout(() => {
        window.location.href = "{{ $redirectUrl ?? route('reports.menu') }}";
    }, 2000);

};
</script>
</body>

</html>
