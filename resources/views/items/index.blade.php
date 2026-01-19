<div style="display:flex; justify-content: space-between; align-items: center;">
    <h1>Items</h1>
    <nav>
        <a href="{{ url('/') }}">Dashboard</a> |
        <a href="{{ route('items.index') }}" style="text-decoration: underline;">Items</a> |
        <a href="{{ route('services.index') }}">Services</a> |
        <a href="{{ route('categories.index') }}">Categories</a> |
        <a href="{{ route('locations.index') }}">Locations</a> |
        <a href="{{ route('settings.index') }}" style="color: #52c41a;">Settings</a>
    </nav>
</div>

<a href="#" onclick="replacePage('{{ route('items.create') }}')"
    style="background: #1890ff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px;">Create
    New Item</a>

@if (session('success'))
    <div style="color: green; background: #e6ffed; padding: 10px; border: 1px solid green; margin-bottom: 10px;">
        {{ session('success') }}</div>
@endif

@if (session('error'))
    <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin-bottom: 10px;">
        {{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('items.index') }}" onsubmit="cleanEmptyFields(this)"
    style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>Search:</strong>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, code, etc...">

    <br><br>
    <strong>Filters:</strong>
    <select name="location_id">
        <option value="">-- All Locations --</option>
        @foreach ($locations as $l)
            <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>
                {{ $l->name }}</option>
        @endforeach
    </select>

    <select name="category_id">
        <option value="">-- All Categories --</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                {{ $c->name }}</option>
        @endforeach
    </select>

    <select name="condition">
        <option value="">-- All Conditions --</option>
        <option value="baik" {{ request('condition') == 'baik' ? 'selected' : '' }}>Baik</option>
        <option value="rusak" {{ request('condition') == 'rusak' ? 'selected' : '' }}>Rusak</option>
        <option value="perbaikan" {{ request('condition') == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
        <option value="dimusnahkan" {{ request('condition') == 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
    </select>

    <select name="service_status">
        <option value="">-- Maintenance Status --</option>
        <option value="kelewatan" {{ request('service_status') == 'kelewatan' ? 'selected' : '' }}>Kelewatan</option>
        <option value="akan_datang" {{ request('service_status') == 'akan_datang' ? 'selected' : '' }}>Akan Datang
        </option>
        <option value="jatuh_tempo" {{ request('service_status') == 'jatuh_tempo' ? 'selected' : '' }}>Jatuh Tempo Hari
            Ini</option>
    </select>

    <button type="submit">Apply</button>
    <a href="{{ route('items.index') }}">Reset</a>
</form>

<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #eee;">
            <th>Photo</th>
            <th>Code</th>
            <th>Name</th>
            <th>Location</th>
            <th>Condition</th>
            <th>Maintenance Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>
                    @if ($item->photo_path)
                        <img src="{{ asset('storage/' . $item->photo_path) }}" width="40"
                            style="border-radius:4px;">
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->uqcode }}</td>
                <td>
                    <strong>{{ $item->name }}</strong><br>
                    <small>{{ $item->category->name }}</small>
                </td>
                <td>{{ $item->location->name }}</td>
                <td style="text-transform: capitalize;">{{ $item->condition }}</td>
                <td>
                    <div style="margin-bottom: 5px;">
                        <strong>{{ $item->service_status_label }}</strong><br>
                        <small>Terakhir Maintenance:
                            {{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : '-' }}</small>
                    </div>

                    @if ($item->service_required && !$item->services()->whereNull('date_out')->exists())
                        @php
                            $today = now()->startOfDay();
                            $next = $item->next_service_date ? $item->next_service_date->startOfDay() : null;
                            $targetTab = $next && $next > $today ? 'upcoming' : 'needs_service';
                        @endphp
                        <a href="{{ route('services.index', ['tab' => $targetTab, 'search' => $item->uqcode]) }}"
                            style="background: #ff4d4f; color: white; padding: 2px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8em; display: inline-block;">
                            Lakukan Maintenance Berkala
                        </a>
                    @endif
                </td>
                <td>
                    <div style="display:flex; flex-direction: column; gap: 5px;">
                        @if (!$item->services()->whereNull('date_out')->exists())
                            <a href="#" onclick="replacePage('{{ route('items.service.create', $item->id) }}')"
                                style="background: #1890ff; color: white; padding: 4px 8px; text-decoration: none; border-radius: 4px; font-size: 0.9em; text-align: center;">
                                Serviskan (Manual/Perbaikan)
                            </a>
                        @else
                            <span style="color: #666; font-size: 0.85em; font-style: italic;">Sedang dalam proses
                                servis</span>
                        @endif

                        <div style="display:flex; gap: 5px; justify-content: center;">
                            <a href="#" onclick="replacePage('{{ route('items.edit', $item->id) }}')">Edit</a>
                            <form action="{{ route('items.destroy', $item->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure?')"
                                    style="background:none; border:none; color:red; cursor:pointer; padding:0; font-size:1em;">Delete</button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;">No data found</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div style="margin-top: 15px;">
        {{ $items->links() }}
    </div>
@endif

<script>
    function cleanEmptyFields(form) {
        const elements = form.elements;
        for (let i = 0; i < elements.length; i++) {
            const el = elements[i];
            if (el.name && !el.value && el.name !== 'pagination' && el.tagName !== 'BUTTON') {
                el.name = '';
            }
        }
    }

    function replacePage(url) {
        window.location.replace(url);
    }
</script>

<br>
<a href="{{ url('/') }}">Back to Dashboard</a>
