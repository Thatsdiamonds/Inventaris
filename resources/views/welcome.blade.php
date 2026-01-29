@extends('layouts.app')

@section('content')
    <h1>{{ $appName }}</h1>

    <div class="fast_data_wrap" style="display: flex; width: 100%; justify-content: space-between; margin-bottom: 30px;">
        <div class="fast_data" style="text-align: center;">
            <h2 style="margin: 0;">{{ $data['total_items'] }}</h2>
            <p style="margin: 5px 0 0;">Total Inventaris</p>
        </div>
        <div class="fast_data" style="text-align: center;">
            <h2 style="margin: 0;">{{ $data['total_categories'] }}</h2>
            <p style="margin: 5px 0 0;">Kategori</p>
        </div>
        <div class="fast_data" style="text-align: center;">
            <h2 style="margin: 0;">{{ $data['total_locations'] }}</h2>
            <p style="margin: 5px 0 0;">Lokasi</p>
        </div>
        <div class="fast_data" style="text-align: center;">
            <h2 style="margin: 0;">{{ $data['total_dimusnahkan'] }}</h2>
            <p style="margin: 5px 0 0;">Dimusnahkan</p>
        </div>
        <div class="fast_data" style="text-align: center;">
            <h2 style="margin: 0;">{{ $data['total_perbaikan'] }}</h2>
            <p style="margin: 5px 0 0;">Dalam Perbaikan</p>
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

    <section>
        <h2>Peringatan Servis</h2>

        <div style="display:flex; gap: 20px; flex-wrap: wrap;">
            {{-- Override Card Status --}}
            <div
                style="flex:1; min-width: 250px; background: #fff1f0; padding: 15px; border-radius: 8px; border: 1px solid #ffa39e;">
                <h3 style="margin-top:0; color: #cf1322;">Terlambat</h3>
                @if ($data['overdue_latest'])
                    <div style="margin-bottom: 10px;">
                        <strong>{{ $data['overdue_latest']->name }}</strong><br>
                        <small>{{ $data['overdue_latest']->uqcode }}</small>
                    </div>
                @else
                    <p>Tidak ada data terlambat.</p>
                @endif
                <a href="{{ route('services.index', ['tab' => 'needs_service']) }}" wire:navigate
                    style="font-weight:bold; color: #cf1322; text-decoration: none;">
                    Total: {{ $data['overdue_count'] }}
                </a>
            </div>

            <div
                style="flex:1; min-width: 250px; background: #e6f7ff; padding: 15px; border-radius: 8px; border: 1px solid #91d5ff;">
                <h3 style="margin-top:0; color: #096dd9;">Akan Datang</h3>
                @if ($data['upcoming_latest'])
                    <div style="margin-bottom: 10px;">
                        <strong>{{ $data['upcoming_latest']->name }}</strong><br>
                        <small>{{ $data['upcoming_latest']->uqcode }}</small>
                    </div>
                @else
                    <p>Tidak ada jadwal terdekat.</p>
                @endif
                <a href="{{ route('services.index', ['tab' => 'upcoming']) }}" wire:navigate
                    style="font-weight:bold; color: #096dd9; text-decoration: none;">
                    Total: {{ $data['upcoming_count'] }}
                </a>
            </div>

            <div
                style="flex:1; min-width: 250px; background: #f6ffed; padding: 15px; border-radius: 8px; border: 1px solid #b7eb8f;">
                <h3 style="margin-top:0; color: #389e0d;">Sedang Servis</h3>
                @if ($data['in_service_latest'])
                    <div style="margin-bottom: 10px;">
                        <strong>{{ $data['in_service_latest']->item->name }}</strong><br>
                        <small>Vendor: {{ $data['in_service_latest']->vendor }}</small>
                    </div>
                @else
                    <p>Tidak ada barang sedang servis.</p>
                @endif
                <a href="{{ route('services.index', ['tab' => 'in_service']) }}" wire:navigate
                    style="font-weight:bold; color: #389e0d; text-decoration: none;">
                    Total: {{ $data['in_service_count'] }}
                </a>
            </div>
        </div>
    </section>
@endsection
