<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        :root {
            --color-primary: #2563eb;
            --color-bg: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: var(--color-bg);
            overflow: hidden;
        }

        .container {
            padding: 2.5rem;
            border-radius: 1.5rem;
            max-width: 400px;
            width: 90%;
            position: relative;
            z-index: 10;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--color-primary);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 0.8s linear infinite;
            margin-bottom: 1.5rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        h2 {
            color: #1e293b;
            margin: 0 0 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        p {
            color: #64748b;
            font-size: 14px;
            margin: 0 0 2rem;
            line-height: 1.5;
        }

        .progress-wrapper {
            background: #f1f5f9;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .progress-bar {
            background: var(--color-primary);
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.875rem;
            color: #475569;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="loader" id="loader"></div>
        <h2 id="status_title">{{ $title }}</h2>
        <p id="status_message">{{ $message }}</p>

        <div class="progress-wrapper">
            <div class="progress-bar" id="progress_bar"></div>
        </div>
        <div class="progress-text" id="progress_text">Menyiapkan...</div>
    </div>

    <script>
        const config = {
            type: "{{ $type }}",
            id: {{ $id }},
            new_code: "{{ $newCode }}",
            excluded_items: @json($excludedItems),
            total: {{ $total }},
            stepUrl: "{{ route('items.process_update.step') }}",
            checkUrl: "{{ route('items.process_update.check') }}",
            redirectUrl: "{{ $redirectUrl }}",
            csrf: "{{ csrf_token() }}",
            workers: 3, // Initial concurrent requests
            batchSize: 50
        };

        let processed = 0;
        let nextOffset = 0;
        let activeWorkers = 0;
        let isFinished = false;
        let isChecking = false;
        let errorCount = 0;

        async function startWorker() {
            if (isFinished || isChecking) return;

            if (nextOffset >= config.total) {
                if (activeWorkers === 0 && !isChecking) {
                    runFinalCheck();
                }
                return;
            }

            activeWorkers++;
            const currentOffset = nextOffset;
            nextOffset += config.batchSize;

            try {
                const response = await fetch(config.stepUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    },
                    body: JSON.stringify({
                        type: config.type,
                        id: config.id,
                        new_code: config.new_code,
                        excluded_items: config.excluded_items,
                        offset: currentOffset,
                        limit: config.batchSize
                    })
                });

                if (!response.ok) throw new Error('Request failed');

                const result = await response.json();
                processed += result.processed;
                activeWorkers--;

                // Update UI
                const percent = Math.min(100, Math.round((processed / config.total) * 100));
                document.getElementById('progress_bar').style.width = percent + '%';
                document.getElementById('progress_text').innerText = `${processed} / ${config.total}`;

                if (processed >= config.total || nextOffset >= config.total) {
                    if (activeWorkers === 0 && !isChecking) {
                        runFinalCheck();
                    }
                } else {
                    startWorker();
                }
            } catch (error) {
                console.error('Worker error:', error);
                activeWorkers--;
                errorCount++;

                if (errorCount > 15) {
                    isFinished = true;
                    document.getElementById('status_title').innerText = "Gagal Memproses";
                    document.getElementById('status_message').innerText = "Terlalu banyak kesalahan koneksi.";
                    document.getElementById('loader').style.display = 'none';
                } else {
                    nextOffset -= config.batchSize;
                    setTimeout(startWorker, 2000);
                }
            }
        }

        async function runFinalCheck() {
            if (isChecking) return;
            isChecking = true;

            document.getElementById('status_title').innerText = "Memverifikasi Data...";
            document.getElementById('status_message').innerText = "Mengecek konsistensi kode akhir...";
            document.getElementById('progress_text').innerText = "Hampir selesai...";

            try {
                const response = await fetch(config.checkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    },
                    body: JSON.stringify({
                        type: config.type,
                        id: config.id,
                        new_code: config.new_code,
                        excluded_items: config.excluded_items
                    })
                });

                const result = await response.json();

                if (result.success) {
                    finishProcess();
                } else {
                    document.getElementById('loader').style.display = 'none';
                    document.getElementById('status_title').innerText = "Sinkronisasi Parsial";
                    document.getElementById('status_message').innerHTML =
                        `Terdapat <strong>${result.inconsistent} barang</strong> yang gagal diperbarui (mungkin karena tabrakan kode).<br>Silakan cek manual setelah dialihkan.`;

                    setTimeout(() => {
                        window.location.replace(config.redirectUrl);
                    }, 4000);
                }
            } catch (e) {
                finishProcess(); // Fallback to redirect if check fails
            }
        }

        function finishProcess() {
            isFinished = true;
            document.getElementById('loader').style.display = 'none';
            document.getElementById('status_title').innerText = "Pembaruan Selesai!";
            document.getElementById('status_message').innerText = "Seluruh data telah diverifikasi.";
            document.getElementById('progress_text').innerText = "Mengalihkan...";

            setTimeout(() => {
                window.location.replace(config.redirectUrl);
            }, 800);
        }

        window.onload = function() {
            for (let i = 0; i < config.workers; i++) {
                setTimeout(() => startWorker(), i * 300);
            }
        };
    </script>
</body>

</html>
