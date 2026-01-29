@extends('layouts.app')

@section('content')
    <style>
        .filter-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .item-checkbox {
            transform: scale(1.2);
            margin-right: 8px;
        }

        .btn-generate {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-reset {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Existing styles moved and kept if not replaced by new ones */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByName('item_ids[]');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateCount();
        }

        function updateCount() {
            checkboxes = document.getElementsByName('item_ids[]');
            let count = 0;
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                if (checkboxes[i].checked) count++;
            }
            document.getElementById('selected_count').innerText = count;
        }
    </script>

    <div
        style="display:flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
        <h1>QR Code Generator</h1>
    </div>

    @if (session('error'))
        <div style="color: red; padding: 10px; background: #ffe6e6; margin-bottom: 10px;">{{ session('error') }}</div>
    @endif

    <div class="filter-box">
        <form method="GET" action="{{ route('qr.index') }}">
            <strong>Filter List:</strong>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." style="padding: 4px;">

            <select name="location_id" style="padding: 4px;">
                <option value="">-- All Locations --</option>
                @foreach ($locations as $l)
                    <option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>
                        {{ $l->name }}</option>
                @endforeach
            </select>

            <select name="category_id" style="padding: 4px;">
                <option value="">-- All Categories --</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-apply">Apply Filter</button>
            <a href="{{ route('qr.index') }}" wire:navigate style="font-size: 0.9em; margin-left: 10px;">Reset</a>

        </form>
    </div>

    <form method="POST" action="{{ route('qr.generate') }}" target="_blank">
        @csrf

        <div
            style="background: #e2e3e5; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display:flex; gap: 20px; align-items: center;">
                <div>
                    <strong>Configuration:</strong> <br>
                    <span>Organization: <strong>{{ $appName }}</strong></span>
                </div>
                <div>
                    <strong>Format:</strong> <br>
                    <select name="format" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="pdf">PDF (Printable)</option>
                        <option value="zip">ZIP (Images)</option>
                    </select>
                </div>
            </div>
            <div style="text-align: right;">
                <h3 style="margin: 0;">Selected Items: <span id="selected_count">0</span></h3>
                <p style="margin: 5px 0 0 0; color: #666;">(Total List: {{ $items->count() }})</p>
                <button type="submit" class="btn-primary" style="margin-top: 10px;">Generate Labels</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40"><input type="checkbox" onclick="toggleAll(this)"></th>
                    <th>Unique Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><input type="checkbox" name="item_ids[]" value="{{ $item->id }}" onchange="updateCount()">
                        </td>
                        <td>{{ $item->uqcode }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td>{{ $item->location->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </form>
@endsection
