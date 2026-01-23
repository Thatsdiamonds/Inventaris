<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inventaris Dashboard</title>
    <style>
        .fast_data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            width: fit-content;
            height: fit-content;
        }

        .fast_data_wrap {
            display: flex;
            width: 100%;
            justify-content: space-between;
        }

        nav a {
            text-decoration: none;
            font-weight: bold;
            color: #1890ff;
        }
    </style>
</head>

<body>

    @auth
        <h1>{{ $appName }}</h1>
        <div style="display:flex; justify-content: space-between; align-items: center;">
            <nav>
                <a href="{{ route('items.index') }}">Items</a> |
                <a href="{{ route('services.index') }}">Services</a> |
                <a href="{{ route('categories.index') }}">Categories</a> |
                <a href="{{ route('locations.index') }}">Locations</a> |
                <a href="{{ route('settings.index') }}" style="color: #52c41a;">Settings</a> |
                <a href="{{ route('qr.index') }}" style="font-weight:bold; color: #722ed1;">QR Generator</a>
            </nav>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Logout ({{ Auth::user()->name }})</button>
               <a href="{{ route('reports.menu') }}" class="btn btn-primary">Report</a>
            </form>
        </div>

        <hr>

        <div class="fast_data_wrap">
            <div class="fast_data">
                <h2>{{ $data['total_items'] }}</h2>
                <p>Total Inventaris</p>
            </div>
            <div class="fast_data">
                <h2>{{ $data['total_categories'] }}</h2>
                <p>Jumlah Kategori</p>
            </div>
            <div class="fast_data">
                <h2>{{ $data['total_locations'] }}</h2>
                <p>Jumlah Lokasi</p>
            </div>
            <div class="fast_data">
                <h2>{{ $data['total_dimusnahkan'] }}</h2>
                <p>Jumlah Item Dimusnahkan</p>
            </div>
            <div class="fast_data">
                <h2>{{ $data['total_perbaikan'] }}</h2>
                <p>Jumlah Item Dalam Perbaikan</p>
            </div>
        </div>

        <hr>

        <section>
            <h2>Peringatan Servis</h2>

            <div style="display:flex; gap: 20px; flex-wrap: wrap;">
                <div
                    style="flex:1; min-width: 250px; background: #fff1f0; padding: 15px; border-radius: 8px; border: 1px solid #ffa39e;">
                    <h3 style="margin-top:0; color: #cf1322;">Terlambat</h3>
                    @if ($data['overdue_latest'])
                        <div style="margin-bottom: 10px;">
                            <a href="{{ route('items.index', ['search' => $data['overdue_latest']->uqcode]) }}"
                                style="text-decoration: none; color: inherit; display:flex; gap: 10px; align-items: center;">
                                @if ($data['overdue_latest']->photo_path)
                                    <img src="{{ asset('storage/' . $data['overdue_latest']->photo_path) }}" width="60"
                                        height="60" style="object-fit: cover; border-radius: 4px;">
                                @endif
                                <div>
                                    <strong>{{ $data['overdue_latest']->name }}</strong><br>
                                    <small>{{ $data['overdue_latest']->uqcode }}</small>
                                </div>
                            </a>
                        </div>
                    @else
                        <p>Tidak ada data terlambat.</p>
                    @endif
                    <a href="{{ route('services.index', ['tab' => 'needs_service']) }}"
                        style="font-weight:bold; color: #cf1322;">
                        Total Terlambat: {{ $data['overdue_count'] }}
                    </a>
                </div>

                <div
                    style="flex:1; min-width: 250px; background: #e6f7ff; padding: 15px; border-radius: 8px; border: 1px solid #91d5ff;">
                    <h3 style="margin-top:0; color: #096dd9;">Akan Datang</h3>
                    @if ($data['upcoming_latest'])
                        <div style="margin-bottom: 10px;">
                            <a href="{{ route('items.index', ['search' => $data['upcoming_latest']->uqcode]) }}"
                                style="text-decoration: none; color: inherit; display:flex; gap: 10px; align-items: center;">
                                @if ($data['upcoming_latest']->photo_path)
                                    <img src="{{ asset('storage/' . $data['upcoming_latest']->photo_path) }}"
                                        width="60" height="60" style="object-fit: cover; border-radius: 4px;">
                                @endif
                                <div>
                                    <strong>{{ $data['upcoming_latest']->name }}</strong><br>
                                    <small>{{ $data['upcoming_latest']->uqcode }}</small>
                                </div>
                            </a>
                        </div>
                    @else
                        <p>Tidak ada jadwal servis terdekat.</p>
                    @endif
                    <a href="{{ route('services.index', ['tab' => 'upcoming']) }}"
                        style="font-weight:bold; color: #096dd9;">
                        Total Akan Datang: {{ $data['upcoming_count'] }}
                    </a>
                </div>

                <div
                    style="flex:1; min-width: 250px; background: #f6ffed; padding: 15px; border-radius: 8px; border: 1px solid #b7eb8f;">
                    <h3 style="margin-top:0; color: #389e0d;">Sedang Servis</h3>
                    @if ($data['in_service_latest'])
                        <div style="margin-bottom: 10px;">
                            <a href="{{ route('items.index', ['search' => $data['in_service_latest']->item->uqcode]) }}"
                                style="text-decoration: none; color: inherit; display:flex; gap: 10px; align-items: center;">
                                @if ($data['in_service_latest']->item->photo_path)
                                    <img src="{{ asset('storage/' . $data['in_service_latest']->item->photo_path) }}"
                                        width="60" height="60" style="object-fit: cover; border-radius: 4px;">
                                @endif
                                <div>
                                    <strong>{{ $data['in_service_latest']->item->name }}</strong><br>
                                    <small>Vendor: {{ $data['in_service_latest']->vendor }}</small>
                                </div>
                            </a>
                        </div>
                    @else
                        <p>Tidak ada barang sedang diservis.</p>
                    @endif
                    <a href="{{ route('services.index', ['tab' => 'in_service']) }}"
                        style="font-weight:bold; color: #389e0d;">
                        Total Servis: {{ $data['in_service_count'] }}
                    </a>
                </div>
            </div>
        </section>
    @else
        <script>
            window.location.replace("{{ route('login') }}");
        </script>
    @endauth

    <script>
        // UX improvement: No history for certain navigations
        function replacePage(url) {
            window.location.replace(url);
        }
    </script>

</body>

</html>
