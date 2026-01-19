<h1>Services Management</h1>

<div style="margin-bottom: 20px;">
    <a href="{{ route('services.index', ['tab' => 'in_service']) }}"
        style="padding: 10px; text-decoration: none; border-bottom: 3px solid {{ $tab == 'in_service' ? '#1890ff' : 'transparent' }}; color: {{ $tab == 'in_service' ? '#1890ff' : '#666' }}; font-weight: bold;">
        Sedang Diservis
        <span
            style="background: red; color: white; border-radius: 10px; padding: 2px 8px; font-size: 0.8em;">{{ $counts['in_service'] }}</span>
    </a>
    &nbsp;&nbsp;
    <a href="{{ route('services.index', ['tab' => 'needs_service']) }}"
        style="padding: 10px; text-decoration: none; border-bottom: 3px solid {{ $tab == 'needs_service' ? '#1890ff' : 'transparent' }}; color: {{ $tab == 'needs_service' ? '#1890ff' : '#666' }}; font-weight: bold;">
        Perlu Servis
        <span
            style="background: orange; color: white; border-radius: 10px; padding: 2px 8px; font-size: 0.8em;">{{ $counts['needs_service'] }}</span>
    </a>
    &nbsp;&nbsp;
    <a href="{{ route('services.index', ['tab' => 'upcoming']) }}"
        style="padding: 10px; text-decoration: none; border-bottom: 3px solid {{ $tab == 'upcoming' ? '#1890ff' : 'transparent' }}; color: {{ $tab == 'upcoming' ? '#1890ff' : '#666' }}; font-weight: bold;">
        Akan Datang
        <span
            style="background: #1890ff; color: white; border-radius: 10px; padding: 2px 8px; font-size: 0.8em;">{{ $counts['upcoming'] }}</span>
    </a>
    &nbsp;&nbsp;
    <a href="{{ route('services.index', ['tab' => 'completed']) }}"
        style="padding: 10px; text-decoration: none; border-bottom: 3px solid {{ $tab == 'completed' ? '#1890ff' : 'transparent' }}; color: {{ $tab == 'completed' ? '#1890ff' : '#666' }}; font-weight: bold;">
        Riwayat Selesai
    </a>
    &nbsp;&nbsp;
    <a href="{{ route('services.index', ['tab' => 'all']) }}"
        style="padding: 10px; text-decoration: none; border-bottom: 3px solid {{ $tab == 'all' ? '#1890ff' : 'transparent' }}; color: {{ $tab == 'all' ? '#1890ff' : '#666' }}; font-weight: bold;">
        Semua Terkait Servis
    </a>
</div>

<form method="GET" action="{{ route('services.index') }}"
    style="margin-bottom: 20px; background: #f4f4f4; padding: 15px; border-radius: 8px;"
    onsubmit="cleanEmptyFields(this)">
    <input type="hidden" name="tab" value="{{ $tab }}">

    <div style="display:flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <div>
            <strong>Search:</strong><br>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Barang, kode, vendor..."
                style="padding: 5px; width: 200px;">
        </div>

        <div>
            <strong>Location:</strong><br>
            <select name="location_id" style="padding: 5px;">
                <option value="">-- All Locations --</option>
                @foreach ($locations as $l)
                    <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>
                        {{ $l->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <strong>Category:</strong><br>
            <select name="category_id" style="padding: 5px;">
                <option value="">-- All Categories --</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="align-self: flex-end;">
            <button type="submit" style="padding: 6px 15px; cursor: pointer;">Saring</button>
            @if (request()->anyFilled(['search', 'location_id', 'category_id']))
                <a href="{{ route('services.index', ['tab' => $tab]) }}"
                    style="margin-left: 5px; font-size: 0.9em;">Reset</a>
            @endif
        </div>
    </div>
</form>

@if (session('success'))
    <div style="background: #e6ffed; border: 1px solid #52c41a; color: #52c41a; padding: 10px; margin-bottom: 15px;">
        {{ session('success') }}
    </div>
@endif

@php
    $isEmpty = false;
    if ($tab == 'in_service' && $inService->isEmpty()) {
        $isEmpty = true;
    }
    if ($tab == 'needs_service' && $needsService->isEmpty()) {
        $isEmpty = true;
    }
    if ($tab == 'upcoming' && $upcoming->isEmpty()) {
        $isEmpty = true;
    }
    if ($tab == 'completed' && $completed->isEmpty()) {
        $isEmpty = true;
    }
    if ($tab == 'all' && $allItems->isEmpty()) {
        $isEmpty = true;
    }
@endphp

@if ($isEmpty && request('search'))
    <div
        style="background: #fffbe6; border: 1px solid #ffe58f; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
        <p>Tidak ada hasil dalam tab <strong>"{{ str_replace('_', ' ', $tab) }}"</strong>.</p>
        <p>Coba cari di <strong><a
                    href="{{ route('services.index', array_merge(request()->all(), ['tab' => 'all'])) }}">Semua Tab
                    Terkait Servis</a></strong>?</p>
    </div>
@endif

@if ($tab == 'in_service')
    <h3>Barang Sedang Diservis (Dalam Proses)</h3>
    <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f5f5f5;">
            <tr>
                <th>Photo</th>
                <th>Item / Code</th>
                <th>Location / Cat</th>
                <th>Vendor</th>
                <th>Tanggal Masuk</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inService as $s)
                <tr>
                    <td>
                        @if ($s->item->photo_path)
                            <img src="{{ asset('storage/' . $s->item->photo_path) }}" width="40"
                                style="border-radius: 4px;">
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <strong>{{ $s->item->name }}</strong><br>
                        <small>{{ $s->item->uqcode }}</small>
                    </td>
                    <td>
                        {{ $s->item->location->name }}<br>
                        <small>{{ $s->item->category->name }}</small>
                    </td>
                    <td>{{ $s->vendor }}</td>
                    <td>{{ $s->date_in->format('d/m/Y') }}</td>
                    <td>{{ $s->description }}</td>
                    <td>
                        <button onclick="openFinishModal('{{ $s->id }}', '{{ $s->item->name }}')"
                            style="background:#52c41a; color:white; border:none; padding:5px 10px; cursor:pointer; margin-bottom:2px; border-radius:3px;">Update
                            Selesai</button><br>
                        <button onclick="openFailModal('{{ $s->id }}', '{{ $s->item->name }}')"
                            style="background:#ff4d4f; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:3px;">Update
                            Gagal</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999;">Tidak ada barang yang sedang diservis.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Finish Modal -->
    <div id="finishModal"
        style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
        <div
            style="background-color:#fefefe; margin:10% auto; padding:20px; border:1px solid #888; width:400px; max-width:90%; border-radius: 8px;">
            <h3>Update Selesai: <span id="finishItemName"></span></h3>
            <form id="finishForm" method="POST">
                @csrf
                <label>Tanggal Keluar:</label><br>
                <input type="date" name="date_out" value="{{ date('Y-m-d') }}" required
                    style="width:100%; padding:5px;"><br><br>

                <label>Biaya:</label><br>
                <input type="number" name="cost" placeholder="Rp..." style="width:100%; padding:5px;"><br><br>

                <label>Kondisi Akhir:</label><br>
                <select name="condition_after" required style="width:100%; padding:5px;">
                    <option value="baik">Baik</option>
                    <option value="perbaikan">Selesai (Perlu Perbaikan Mendatang)</option>
                    <option value="rusak">Rusak</option>
                </select><br><br>

                <button type="submit"
                    style="background: #52c41a; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Simpan</button>
                <button type="button" onclick="closeFinishModal()"
                    style="padding: 10px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">Batal</button>
            </form>
        </div>
    </div>

    <!-- Fail Modal -->
    <div id="failModal"
        style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
        <div
            style="background-color:#fefefe; margin:10% auto; padding:20px; border:1px solid #888; width:400px; max-width:90%; border-radius: 8px;">
            <h3>Update Gagal: <span id="failItemName"></span></h3>
            <p>Barang akan otomatis dicap sebagai <strong>"Rusak"</strong>.</p>
            <form id="failForm" method="POST">
                @csrf
                <label>Tanggal Gagal:</label><br>
                <input type="date" name="date_out" value="{{ date('Y-m-d') }}" required
                    style="width:100%; padding:5px;"><br><br>

                <button type="submit"
                    style="background: #ff4d4f; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Konfirmasi
                    Gagal</button>
                <button type="button" onclick="closeFailModal()"
                    style="padding: 10px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">Batal</button>
            </form>
        </div>
    </div>
@elseif($tab == 'needs_service')
    <h3>Barang Perlu Servis (Overdue / Jatuh Tempo)</h3>
    <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #fff7e6;">
            <tr>
                <th>Photo</th>
                <th>Item / Code</th>
                <th>Location / Cat</th>
                <th>Status Servis</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($needsService as $item)
                <tr>
                    <td>
                        @if ($item->photo_path)
                            <img src="{{ asset('storage/' . $item->photo_path) }}" width="40"
                                style="border-radius:4px;">
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong><br>
                        <small>{{ $item->uqcode }}</small>
                    </td>
                    <td>
                        {{ $item->location->name }}<br>
                        <small>{{ $item->category->name }}</small>
                    </td>
                    <td>
                        <span style="color: red; font-weight: bold;">{{ $item->service_status_label }}</span><br>
                        <small>Terakhir:
                            {{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : 'Belum pernah' }}</small>
                    </td>
                    <td>
                        <a href="#" onclick="smartNavigate('{{ route('items.service.create', $item->id) }}')"
                            style="background: #1890ff; color: white; padding: 5px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">Mulai
                            Servis</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Tidak ada barang yang memerlukan
                        servis segera.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@elseif($tab == 'upcoming')
    <h3>Barang Akan Datang (Jadwal Servis)</h3>
    <div style="margin-bottom: 15px;">
        Filter Waktu:
        <a href="{{ route('services.index', array_merge(request()->all(), ['tab' => 'upcoming', 'upcoming_filter' => '30_days'])) }}"
            style="text-decoration: {{ request('upcoming_filter', '30_days') == '30_days' ? 'underline' : 'none' }}; font-weight: {{ request('upcoming_filter', '30_days') == '30_days' ? 'bold' : 'normal' }}; margin-right: 10px;">
            30 Hari Kedepan
        </a> |
        <a href="{{ route('services.index', array_merge(request()->all(), ['tab' => 'upcoming', 'upcoming_filter' => 'all'])) }}"
            style="text-decoration: {{ request('upcoming_filter') == 'all' ? 'underline' : 'none' }}; font-weight: {{ request('upcoming_filter') == 'all' ? 'bold' : 'normal' }}; margin-left: 10px;">
            Lihat Semua Jadwal
        </a>
    </div>
    <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #e6f7ff;">
            <tr>
                <th>Photo</th>
                <th>Item / Code</th>
                <th>Location / Cat</th>
                <th>Jadwal Servis</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($upcoming as $item)
                <tr>
                    <td>
                        @if ($item->photo_path)
                            <img src="{{ asset('storage/' . $item->photo_path) }}" width="40"
                                style="border-radius:4px;">
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong><br>
                        <small>{{ $item->uqcode }}</small>
                    </td>
                    <td>
                        {{ $item->location->name }}<br>
                        <small>{{ $item->category->name }}</small>
                    </td>
                    <td>
                        <span style="color: #1890ff; font-weight: bold;">{{ $item->service_status_label }}</span><br>
                        <small>Target:
                            {{ $item->next_service_date ? $item->next_service_date->format('d/m/Y') : '-' }}</small>
                    </td>
                    <td>
                        <a href="#" onclick="smartNavigate('{{ route('items.service.create', $item->id) }}')"
                            style="background: #52c41a; color: white; padding: 5px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">Servis
                            Lebih Awal</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Tidak ada jadwal servis dalam 30 hari
                        ke depan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@elseif($tab == 'completed')
    <h3>Riwayat Servis Selesai</h3>
    <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f5f5f5;">
            <tr>
                <th>Photo</th>
                <th>Item / Code</th>
                <th>Vendor</th>
                <th>Durasi</th>
                <th>Biaya</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($completed as $s)
                <tr>
                    <td>
                        @if ($s->item->photo_path)
                            <img src="{{ asset('storage/' . $s->item->photo_path) }}" width="40"
                                style="border-radius:4px;">
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <strong>{{ $s->item->name }}</strong><br>
                        <small>{{ $s->item->uqcode }}</small>
                    </td>
                    <td>{{ $s->vendor }}</td>
                    <td>
                        {{ $s->date_in ? $s->date_in->format('d/m/Y') : '-' }} s/d
                        {{ $s->date_out ? $s->date_out->format('d/m/Y') : '-' }}
                    </td>
                    <td>Rp{{ number_format($s->cost, 0, ',', '.') }}</td>
                    <td>{{ $s->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada riwayat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: 15px;">{{ $completed->links() }}</div>
@elseif($tab == 'all')
    <h3>Semua Barang Terkait Servis (In Service, Due, Upcoming)</h3>
    <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #eee;">
            <tr>
                <th>Photo</th>
                <th>Item / Code</th>
                <th>Location / Cat</th>
                <th>Status Sekarang</th>
                <th>Aksi Cepat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($allItems as $item)
                @php
                    $activeService = $item->services()->whereNull('date_out')->first();
                @endphp
                <tr>
                    <td>
                        @if ($item->photo_path)
                            <img src="{{ asset('storage/' . $item->photo_path) }}" width="40"
                                style="border-radius:4px;">
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong><br>
                        <small>{{ $item->uqcode }}</small>
                    </td>
                    <td>
                        {{ $item->location->name }}<br>
                        <small>{{ $item->category->name }}</small>
                    </td>
                    <td>
                        @if ($activeService)
                            <span style="color: orange; font-weight:bold;">Sedang Diservis</span><br>
                            <small>Masuk: {{ $activeService->date_in->format('d/m/Y') }}</small>
                        @elseif($item->service_required)
                            @php
                                $today = now()->startOfDay();
                                $next = $item->next_service_date ? $item->next_service_date->startOfDay() : null;
                                $isOverdue = $next && $next <= $today;
                            @endphp
                            @if ($isOverdue)
                                <span style="color: red; font-weight:bold;">Perlu Servis</span>
                            @else
                                <span style="color: #1890ff;">Akan Datang
                                    ({{ $item->next_service_date->format('d/m/Y') }})
                                </span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if ($activeService)
                            <a href="{{ route('services.index', ['tab' => 'in_service', 'search' => $item->uqcode]) }}"
                                style="font-size:0.9em;">Lihat Detail Servis</a>
                        @else
                            <a href="#"
                                onclick="smartNavigate('{{ route('items.service.create', $item->id) }}')"
                                style="background:#52c41a; color:white; padding:4px 8px; text-decoration:none; border-radius:3px; font-size:0.9em;">Mulai
                                Servis</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada barang yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: 15px;">{{ $allItems->links() }}</div>
@endif

<script>
    function openFinishModal(id, name) {
        document.getElementById('finishForm').action = "/services/" + id + "/finish";
        document.getElementById('finishItemName').innerText = name;
        document.getElementById('finishModal').style.display = "block";
    }

    function closeFinishModal() {
        document.getElementById('finishModal').style.display = "none";
    }

    function openFailModal(id, name) {
        document.getElementById('failForm').action = "/services/" + id + "/fails";
        document.getElementById('failItemName').innerText = name;
        document.getElementById('failModal').style.display = "block";
    }

    function closeFailModal() {
        document.getElementById('failModal').style.display = "none";
    }

    function cleanEmptyFields(form) {
        const elements = form.elements;
        for (let i = 0; i < elements.length; i++) {
            const el = elements[i];
            if (el.name && !el.value && el.tagName !== 'BUTTON') {
                el.name = '';
            }
        }
    }

    // UX: Prevent 'Create/Edit' from being in back history if desired
    // Using location.replace is a strong way to achieve this.
    function smartNavigate(url) {
        window.location.replace(url);
    }
</script>

<br>
<a href="{{ url('/') }}">Back to Dashboard</a>
