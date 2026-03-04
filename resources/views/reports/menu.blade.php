@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div>
            <h1 class="mb-0">Pusat Laporan Aset</h1>
            <p class="text-secondary">Analisis data inventaris dan riwayat servis secara komprehensif.</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="card mb-4 p-1" style="display: inline-flex; background: var(--color-bg-secondary); border-radius: 12px;">
        <button class="btn btn-tab active" onclick="switchTab(event, 'inventory')">
            <svg class="icon icon-sm mr-2">
                <use href="#icon-inventory"></use>
            </svg>
            Laporan Inventaris
        </button>
       <button class="btn btn-tab" onclick="switchTab(event, 'services')">
            <svg class="icon icon-sm mr-2">
                <use href="#icon-service"></use>
            </svg>
            Laporan Servis
        </button>
    </div>

    <div class="row" style="display: grid; grid-template-columns: 350px 1fr; gap: 1.5rem; align-items: start;">

        <!-- SIDEBAR FILTERS -->
        <div class="card card-sticky">
           <form id="reportForm" method="POST" action="{{ route('reports.inventory.generate') }}">
            @csrf

                <!-- INVENTORY FILTERS -->
                <div id="filter-inventory">
                    <h3 class="section-title">Filter Inventaris</h3>

                    <div class="form-group mb-4">
                        <label>Cakupan Data</label>
                        <select name="scope" id="scope" class="form-select" onchange="toggleScope(this.value)">
                            <option value="semua">Semua Barang</option>
                            <option value="lokasi">Berdasarkan Lokasi</option>
                            <option value="kategori">Berdasarkan Kategori</option>
                            <option value="barang">Barang Spesifik</option>
                        </select>
                    </div>

                    <div id="lokasi-select" class="scope-group mb-4" style="display:none;">
                        <label>Pilih Lokasi</label>
                        <div class="checkbox-grid">
                            @foreach ($locations as $l)
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" name="locations[]" value="{{ $l->id }}"
                                        onchange="updatePreview()">
                                    <span>{{ $l->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="kategori-select" class="scope-group mb-4" style="display:none;">
                        <label>Pilih Kategori</label>
                        <div class="checkbox-grid">
                            @foreach ($categories as $c)
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" name="categories[]" value="{{ $c->id }}"
                                        onchange="updatePreview()">
                                    <span>{{ $c->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label>Kondisi</label>
                        <select name="condition" class="form-select" onchange="updatePreview()">
                            <option value="all">Semua Kondisi</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                            <option value="perbaikan">Dalam Perbaikan</option>
                            <option value="dimusnahkan">Dimusnahkan</option>
                        </select>
                    </div>
                </div>

                <!-- SERVICE FILTERS -->
                <div id="filter-services" style="display:none;">
                    <h3 class="section-title">Filter Servis</h3>

                    <div class="form-group mb-4">
                        <label>Vendor</label>
                        <select name="vendor" class="form-select" onchange="updatePreview()">
                            <option value="all">Semua Vendor</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label>Status Servis</label>
                        <select name="status" class="form-select" onchange="updatePreview()">
                            <option value="all">Semua Status</option>
                            <option value="proses">Dalam Proses</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                </div>

                <!-- COMMON RANGE FILTER -->
                <h3 class="section-title">Rentang Waktu</h3>
                <div class="form-group mb-2">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <input type="date" name="from" id="date_from" class="form-input" onchange="updatePreview()">
                        <input type="date" name="to" id="date_to" class="form-input" onchange="updatePreview()">
                    </div>
                </div>
                <div class="flex flex-wrap gap-1 mb-5">
                    <button type="button" class="btn btn-ghost btn-xs" onclick="setRange(30)">30 Hari</button>
                    <button type="button" class="btn btn-ghost btn-xs" onclick="setRange(365)">1 Tahun</button>
                    <button type="button" class="btn btn-ghost btn-xs" onclick="clearRange()">Reset</button>
                </div>

                <div class="mt-4 pt-4" style="border-top: 1px solid var(--color-border-light);">
                    <button type="submit" class="btn btn-primary w-full shadow-sm mb-2" id="submitBtn">
                        <svg class="icon icon-sm mr-2">
                            <use href="#icon-report"></use>
                        </svg>
                        Export Excel
                    </button>
                    <a href="{{ route('reports.layout.edit', 'inventory') }}" id="layoutBtn"
                        class="btn btn-ghost btn-sm w-full">
                        Atur Kolom Laporan
                    </a>
                </div>
            </form>
        </div>

        <!-- MAIN PREVIEW AREA -->
        <div class="card" style="min-height: 600px;">
            <div class="preview-header mb-4">
                <h3 class="m-0" style="font-size: 1.1rem;">Preview Data (10 Item Teratas)</h3>
                <div id="preview-loader" style="display:none;">
                    <div class="spinner-sm"></div>
                </div>
            </div>

            <div id="preview-container">
                <div class="text-center py-5 text-muted">
                    <svg class="icon" style="width: 48px; height: 48px; opacity: 0.2; margin-bottom: 1rem;">
                        <use href="#icon-report"></use>
                    </svg>
                    <p>Mempersiapkan preview...</p>
                </div>
            </div>

            <div class="mt-4 p-4"
                style="background: var(--color-bg-secondary); border-radius: 8px; border: 1px dashed var(--color-border);">
                <small class="text-secondary">
                    * Preview hanya menampilkan data terbatas untuk kecepatan. Gunakan tombol <strong>Export Excel</strong>
                    untuk mengunduh laporan lengkap sesuai kriteria di samping.
                </small>
            </div>
        </div>
    </div>

    <style>
        .btn-tab {
            padding: 8px 16px;
            border-radius: 8px;
            background: transparent;
            color: var(--color-text-secondary);
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }

        .btn-tab.active {
            background: white;
            color: var(--color-primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-text-tertiary);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .card-sticky {
            position: sticky;
            top: 1.5rem;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: 1fr;
            max-height: 200px;
            overflow-y: auto;
            gap: 4px;
            padding: 8px;
            background: var(--color-bg-secondary);
            border-radius: 6px;
        }

        .table-preview {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .table-preview th {
            text-align: left;
            padding: 12px;
            background: var(--color-bg-secondary);
            color: var(--color-text-secondary);
            font-weight: 600;
            border-bottom: 2px solid var(--color-border-light);
        }

        .table-preview td {
            padding: 12px;
            border-bottom: 1px solid var(--color-border-light);
        }

        .badge {
            padding: 4px 8px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-baik {
            background: #e6f7ff;
            color: #1890ff;
        }

        .badge-rusak {
            background: #fff1f0;
            color: #f5222d;
        }

        .badge-perbaikan {
            background: #fff7e6;
            color: #fa8c16;
        }

        .badge-dimusnahkan {
            background: #f5f5f5;
            color: #595959;
        }

        .spinner-sm {
            width: 20px;
            height: 20px;
            border: 2px solid #eee;
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        let currentTab = 'inventory';
        let previewTimeout;

        function switchTab(e, tab) {
            currentTab = tab;

            document.querySelectorAll('.btn-tab').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');

            document.getElementById('filter-inventory').style.display = tab === 'inventory' ? 'block' : 'none';
            document.getElementById('filter-services').style.display = tab === 'services' ? 'block' : 'none';
            document.getElementById('layoutBtn').style.display = tab === 'inventory' ? 'block' : 'none';

            // Update Form Action
            const form = document.getElementById('reportForm');
          form.action = tab === 'inventory'
    ? "{{ route('reports.inventory.generate') }}"
    : "{{ route('reports.services.generate') }}";

            updatePreview();
        }

        function toggleScope(val) {
            document.querySelectorAll('.scope-group').forEach(el => el.style.display = 'none');
            if (val === 'lokasi') document.getElementById('lokasi-select').style.display = 'block';
            if (val === 'kategori') document.getElementById('kategori-select').style.display = 'block';
            updatePreview();
        }

        function updatePreview() {
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(runPreview, 400);
}
async function runPreview() {
    const container = document.getElementById('preview-container');
    const loader = document.getElementById('preview-loader');
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    formData.append('type', currentTab);

    loader.style.display = 'block';

    try {
        const response = await fetch("{{ route('reports.preview') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        const data = await response.json();
        container.innerHTML = data.html;

    } catch (error) {
        container.innerHTML = `
            <div class="text-center py-5 text-danger">
                Gagal memuat preview.
            </div>
        `;
    } finally {
        loader.style.display = 'none';
    }
}

        function setRange(days) {
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - days);
            document.getElementById('date_to').valueAsDate = end;
            document.getElementById('date_from').valueAsDate = start;
            updatePreview();
        }

        function clearRange() {
            document.getElementById('date_to').value = '';
            document.getElementById('date_from').value = '';
            updatePreview();
        }

        // Init
       document.addEventListener('DOMContentLoaded', () => {
    updatePreview();
});
    </script>
@endsection
