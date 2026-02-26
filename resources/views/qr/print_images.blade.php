@extends('layouts.app')

@section('content')
    <div class="page-header mb-4 no-print">
        <div>
            <h1 class="mb-1">Antrian Cetak Label</h1>
            <p class="text-secondary">Tambahkan label dari halaman Aset, lalu cetak sekaligus.
                Layout: <strong>{{ $layout->name ?? 'Default' }}</strong> ({{ $layout->width }}mm x
                {{ $layout->height }}mm)
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusSemua()" id="btn_hapus_semua"
                style="display: none;">
                <svg class="icon icon-sm">
                    <use href="#icon-trash"></use>
                </svg>
                Hapus Semua
            </button>
            <button type="button" class="btn btn-primary" onclick="window.print()" id="btn_cetak" style="display: none;">
                <svg class="icon icon-sm">
                    <use href="#icon-print"></use>
                </svg>
                Cetak Sekarang
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    <div id="empty_state" class="card no-print mb-4">
        <div style="padding: 3rem; text-align: center;">
            <svg class="icon icon-xl text-muted mb-2" style="width: 48px; height: 48px;">
                <use href="#icon-print"></use>
            </svg>
            <h3 class="mb-1">Belum Ada Label di Antrian</h3>
            <p class="text-sm text-secondary mb-3">Klik ikon <strong>Cetak Label</strong> di halaman Inventaris Aset
                untuk menambahkan label ke antrian ini.</p>
            <a href="{{ route('items.index') }}" wire:navigate class="btn btn-accent btn-sm">
                <svg class="icon icon-sm">
                    <use href="#icon-box"></use>
                </svg>
                Buka Halaman Aset
            </a>
        </div>
    </div>

    {{-- Queue List (card view, no-print) --}}
    <div id="queue_list" class="no-print mb-4" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Daftar Antrian (<span id="queue_count">0</span> label)</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 120px;">Preview</th>
                            <th>Kode Aset</th>
                            <th>Nama Barang</th>
                            <th>Lokasi</th>
                            <th class="text-right" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="queue_tbody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Print Preview Area --}}
    <div id="print_preview_container">
    </div>

    <style>
        :root {
            --label-width: {{ $layout->width }}mm;
            --label-height: {{ $layout->height }}mm;
            --page-width: {{ $layout->paper_size === 'Letter' ? '215.9mm' : '210mm' }};
            --page-height: {{ $layout->paper_size === 'Letter' ? '279.4mm' : '297mm' }};
            --margin-top: {{ $layout->margin_top }}mm;
            --margin-bottom: {{ $layout->margin_bottom }}mm;
            --margin-left: {{ $layout->margin_left }}mm;
            --margin-right: {{ $layout->margin_right }}mm;
            --gap-x: {{ $layout->gap_x }}mm;
            --gap-y: {{ $layout->gap_y }}mm;
        }

        @page {
            size: {{ $layout->paper_size === 'Letter' ? 'Letter' : 'A4' }};
            margin: 0;
        }

        #print_preview_container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .print-page {
            width: var(--page-width);
            height: var(--page-height);
            background: white;
            padding-top: var(--margin-top);
            padding-bottom: var(--margin-bottom);
            padding-left: var(--margin-left);
            padding-right: var(--margin-right);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(2, 100mm);
            grid-template-rows: repeat(9, 30mm);
            gap: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        .image-label-card {
            width: 100mm;
            height: 30mm;
            border: 0.1mm solid #eee;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .image-label-card img {
            width: 100mm;
            height: 30mm;
            display: block;
        }

        .queue-preview-img {
            height: 36px;
            border: 1px solid var(--color-border);
            border-radius: 4px;
        }

        #queue_tbody tr {
            transition: background 0.2s;
        }

        #queue_tbody tr:hover {
            background: var(--color-bg-secondary);
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .sidebar {
                display: none !important;
            }

            .main-container {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .app-wrapper {
                display: block !important;
            }

            body {
                background: white;
            }

            #print_preview_container {
                gap: 0;
            }

            .print-page {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
                border: none;
            }

            .image-label-card {
                border: none;
            }
        }
    </style>

    <script>
        // ====== CROSS-TAB COMMUNICATION ======
        // Uses BroadcastChannel API for inter-tab messaging.
        // The items page sends { action: 'add', itemId: 123 }
        // This page receives it, fetches the label image, and adds to queue.

        const CHANNEL_NAME = 'label-print-queue';
        const channel = new BroadcastChannel(CHANNEL_NAME);

        // Label queue: [{ id, uqcode, name, location, image (base64 dataURL) }]
        let queue = [];

        // Layout calculation constants
        const paperWidth = {{ $layout->paper_size === 'Letter' ? 215.9 : 210 }};
        const paperHeight = {{ $layout->paper_size === 'Letter' ? 279.4 : 297 }};
        const labelWidth = {{ $layout->width }};
        const labelHeight = {{ $layout->height }};
        const marginTop = {{ $layout->margin_top }};
        const marginBottom = {{ $layout->margin_bottom }};
        const marginLeft = {{ $layout->margin_left }};
        const marginRight = {{ $layout->margin_right }};
        const gapX = {{ $layout->gap_x }};
        const gapY = {{ $layout->gap_y }};

        const contentWidth = paperWidth - marginLeft - marginRight;
        const contentHeight = paperHeight - marginTop - marginBottom;
        const cols = Math.floor((contentWidth + gapX + 0.01) / (labelWidth + gapX));
        const rows = Math.floor((contentHeight + gapY + 0.01) / (labelHeight + gapY));
        const labelsPerPage = Math.max(1, cols * rows);

        // ====== LISTEN FOR MESSAGES ======
        channel.onmessage = (event) => {
            const data = event.data;
            if (data.action === 'add' && data.itemId) {
                addItemToQueue(data.itemId);
            } else if (data.action === 'ping') {
                // Respond to ping from items page to confirm this tab is open
                channel.postMessage({
                    action: 'pong'
                });
            }
        };

        // On load, announce presence
        channel.postMessage({
            action: 'pong'
        });

        // ====== CORE FUNCTIONS ======
        async function addItemToQueue(itemId) {
            // Check if already in queue
            if (queue.find(q => q.id === itemId)) {
                renderUI();
                return;
            }

            // Show loading indicator
            showToast('Mengambil label...');

            try {
                const response = await fetch(`/qr/label-image/${itemId}`);
                if (!response.ok) throw new Error('Gagal mengambil data');
                const data = await response.json();

                queue.push({
                    id: data.id,
                    uqcode: data.uqcode,
                    name: data.name,
                    location: data.location,
                    image: data.image
                });

                renderUI();
                showToast(`Label "${data.uqcode}" ditambahkan ke antrian`);
            } catch (error) {
                console.error('Error fetching label:', error);
                showToast('Gagal menambahkan label', true);
            }
        }

        function hapusItem(itemId) {
            queue = queue.filter(q => q.id !== itemId);
            renderUI();
        }

        function hapusSemua() {
            if (queue.length === 0) return;
            if (!confirm('Hapus semua label dari antrian?')) return;
            queue = [];
            renderUI();
        }

        // ====== RENDER ======
        function renderUI() {
            const emptyState = document.getElementById('empty_state');
            const queueList = document.getElementById('queue_list');
            const btnCetak = document.getElementById('btn_cetak');
            const btnHapusSemua = document.getElementById('btn_hapus_semua');
            const queueCount = document.getElementById('queue_count');

            if (queue.length === 0) {
                emptyState.style.display = '';
                queueList.style.display = 'none';
                btnCetak.style.display = 'none';
                btnHapusSemua.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                queueList.style.display = '';
                btnCetak.style.display = 'flex';
                btnHapusSemua.style.display = 'flex';
            }

            queueCount.textContent = queue.length;
            renderQueueTable();
            renderPrintPreview();
        }

        function renderQueueTable() {
            const tbody = document.getElementById('queue_tbody');
            tbody.innerHTML = '';

            queue.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-muted">${index + 1}</td>
                    <td><img src="${item.image}" class="queue-preview-img" alt="Label"></td>
                    <td><code class="text-primary font-bold">${item.uqcode}</code></td>
                    <td class="font-bold">${item.name}</td>
                    <td class="text-secondary">${item.location}</td>
                    <td class="text-right">
                        <button onclick="hapusItem(${item.id})" class="btn btn-ghost btn-sm text-danger" title="Hapus dari antrian">
                            <svg class="icon icon-sm"><use href="#icon-x"></use></svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderPrintPreview() {
            const container = document.getElementById('print_preview_container');
            container.innerHTML = '';

            if (queue.length === 0) return;

            let currentPage = null;

            queue.forEach((item, index) => {
                if (index % labelsPerPage === 0) {
                    currentPage = document.createElement('div');
                    currentPage.className = 'print-page';
                    container.appendChild(currentPage);
                }

                const card = document.createElement('div');
                card.className = 'image-label-card';

                const img = document.createElement('img');
                img.src = item.image;
                img.alt = item.uqcode;

                card.appendChild(img);
                currentPage.appendChild(card);
            });
        }

        // ====== TOAST ======
        function showToast(message, isError = false) {
            // Remove existing toast
            const existing = document.getElementById('queue-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'queue-toast';
            toast.style.cssText = `
                position: fixed; bottom: 20px; right: 20px; z-index: 9999;
                padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 500;
                color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                background: ${isError ? '#ef4444' : '#22c55e'};
                animation: slideUp 0.3s ease;
                transition: opacity 0.3s;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Initial render
        renderUI();
    </script>

    <style>
        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
@endsection
