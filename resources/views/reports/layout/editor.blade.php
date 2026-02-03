@extends('layouts.app')

@section('content')
    <div class="page-header mb-4" style="display: flex; gap: 1rem; align-items: center;">
        <a href="{{ route('reports.menu') }}" class="btn btn-ghost btn-sm"
            style="border-radius: 50%; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
            <svg class="icon icon-sm">
                <use href="#icon-arrow-left"></use>
            </svg>
        </a>
        <div>
            <h1 class="mb-0">Desain Laporan {{ strtoupper($type) }}</h1>
            <p class="text-secondary">Geser kolom (Drag & Drop) untuk mengatur urutan dan data yang ingin ditampilkan.</p>
        </div>
    </div>

    @php
        $activeKeys = is_array($fields) ? $fields : json_decode($fields, true) ?? [];

        // Default fallback if empty (new layout)
        if (empty($activeKeys) && $type == 'inventory') {
            $activeKeys = ['uqcode', 'name', 'category.name', 'location.name', 'condition'];
        }

        $allFields = collect($typeFields);

        // Separate active and inactive
        $activeFields = $allFields
            ->filter(fn($f) => in_array($f['key'], $activeKeys))
            ->sortBy(fn($f) => array_search($f['key'], $activeKeys));

        $inactiveFields = $allFields->filter(fn($f) => !in_array($f['key'], $activeKeys));
    @endphp

    <form id="layoutForm" method="POST" action="{{ route('reports.layout.save', $type) }}">
        @csrf
        <input type="hidden" name="columns" id="columns_input">

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem; align-items: start;">

            <!-- SIDEBAR: AVAILABLE COLUMNS -->
            <div class="card">
                <h3 class="mb-3"
                    style="font-size: 1rem; color: var(--color-text-secondary); text-transform: uppercase; font-weight: 700;">
                    Kolom Tersedia
                </h3>
                <p class="text-muted mb-3" style="font-size: 0.85rem;">Tarik item dari sini ke tabel laporan untuk menambah
                    kolom.</p>

                <div id="inactive-list" class="draggable-list">
                    @foreach ($inactiveFields as $f)
                        <div class="draggable-item" data-key="{{ $f['key'] }}">
                            <svg class="icon icon-sm text-muted mr-2">
                                <use href="#icon-drag"></use>
                            </svg>
                            {{ $f['label'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- MAIN: REPORT SIMULATION -->
            <div>
                <div
                    style="background: white; width: 100%; min-height: 800px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); padding: 40px; border-radius: 4px; position: relative;">

                    <!-- Simulated PDF Header -->
                    <div
                        style="border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px;">
                        <div
                            style="width: 60px; height: 60px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 0.7rem;">
                            LOGO</div>
                        <div>
                            <h1 style="font-size: 1.5rem; font-weight: bold; margin: 0; text-transform: uppercase;">NAMA
                                ORGANISASI</h1>
                            <p style="margin: 5px 0 0; color: #666; font-size: 0.9rem;">Alamat lengkap organisasi akan
                                muncul di sini.</p>
                        </div>
                        <div style="margin-left: auto; text-align: right;">
                            <div style="font-size: 0.8rem; color: #777;">Tanggal Cetak:</div>
                            <strong>{{ date('d-m-Y') }}</strong>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="font-weight: bold; font-size: 1.2rem;">DATA LAPORAN TERPILIH</div>
                        <div style="color: #666;">Periode: Semua Waktu</div>
                    </div>

                    <!-- Draggable Table Header -->
                    <div style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                        <div class="sim-table-row header-row" id="active-list">
                            <!-- Numbering helper (fixed) -->
                            <div class="fixed-col" style="width: 50px; text-align: center;">No</div>

                            @foreach ($activeKeys as $key)
                                @php $f = $allFields->firstWhere('key', $key); @endphp
                                @if ($f)
                                    <div class="draggable-item header-item" data-key="{{ $f['key'] }}">
                                        {{ $f['label'] }}
                                        <span class="remove-btn" onclick="removeColumn(this)">×</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Fake Data Rows -->
                        @for ($i = 1; $i <= 3; $i++)
                            <div class="sim-table-row data-row">
                                <div class="fixed-col" style="width: 50px; text-align: center;">{{ $i }}</div>
                                <div
                                    style="flex: 1; padding: 12px; color: #999; font-style: italic; background:RepeatingLinearGradient(45deg,transparent,transparent 10px,#fafafa 10px,#fafafa 20px);">
                                    Data Contoh...
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div
                        style="margin-top: 40px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px;">
                        <h4 style="font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">RINGKASAN</h4>
                        <div style="display: flex; gap: 40px;">
                            <div>Total: <strong>100</strong></div>
                            <div>Status A: <strong>50</strong></div>
                            <div>Status B: <strong>50</strong></div>
                        </div>
                    </div>

                </div>

                <div class="mt-4 flex justify-end gap-3 sticky-bottom-bar">
                    <a href="{{ route('reports.menu') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">
                        <svg class="icon icon-sm mr-2">
                            <use href="#icon-save"></use>
                        </svg>
                        Simpan Layout Laporan
                    </button>
                </div>
            </div>
        </div>
    </form>

    <style>
        .draggable-list {
            min-height: 200px;
            border: 2px dashed var(--color-border);
            border-radius: var(--radius-md);
            padding: 0.5rem;
            background: var(--color-bg-secondary);
        }

        .draggable-item {
            background: white;
            border: 1px solid var(--color-border);
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            cursor: grab;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: transform 0.1s, box-shadow 0.1s;
        }

        .draggable-item:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        .draggable-item:active {
            cursor: grabbing;
        }

        /* Simulation Table Styles */
        .sim-table-row {
            display: flex;
            border-bottom: 1px solid #ddd;
        }

        .header-row {
            background: #f2f2f2;
            min-height: 45px;
        }

        .fixed-col {
            padding: 12px;
            border-right: 1px solid #ddd;
            font-weight: bold;
            background: #e9ecef;
            color: #555;
        }

        .header-item {
            margin: 4px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            width: auto;
            white-space: nowrap;
            position: relative;
        }

        .header-item .remove-btn {
            margin-left: 8px;
            color: var(--color-danger);
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            line-height: 1;
            opacity: 0.5;
        }

        .header-item .remove-btn:hover {
            opacity: 1;
        }

        /* Sortable Ghost */
        .sortable-ghost {
            opacity: 0.4;
            background: var(--color-bg-tertiary);
        }

        .sortable-drag {
            background: white;
            opacity: 1;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transform: scale(1.02);
        }

        .sticky-bottom-bar {
            position: sticky;
            bottom: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 1rem;
            z-index: 100;
            border: 1px solid var(--color-border);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Initialize Sortable for Available Columns
        new Sortable(document.getElementById('inactive-list'), {
            group: {
                name: 'shared',
                pull: true,
                put: true
            },
            sort: true,
            animation: 150
        });

        // Initialize Sortable for Header Row
        new Sortable(document.getElementById('active-list'), {
            group: {
                name: 'shared',
                pull: true,
                put: true
            },
            sort: true,
            animation: 150,
            filter: '.fixed-col', // Cannot move the fixed "No" column
            onAdd: function(evt) {
                // When dropped into header, maybe change styling slightly if needed
                // But CSS classes handle most usage
            }
        });

        // Form Submission
        document.getElementById('layoutForm').addEventListener('submit', function() {
            let cols = [];
            document.querySelectorAll('#active-list .draggable-item').forEach(el => {
                cols.push(el.dataset.key);
            });
            document.getElementById('columns_input').value = JSON.stringify(cols);
        });

        // Optional: Remove helper
        function removeColumn(element) {
            // Move back to inactive list
            const item = element.closest('.draggable-item');
            const inactiveList = document.getElementById('inactive-list');
            inactiveList.appendChild(item);
        }
    </script>
@endsection
