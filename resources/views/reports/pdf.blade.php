<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 9pt;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            vertical-align: middle;
        }

        .company-info {
            display: inline-block;
            vertical-align: middle;
            margin-left: 15px;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: #111;
        }

        .company-address {
            font-size: 9pt;
            margin: 4px 0 0;
            color: #555;
            max-width: 400px;
        }

        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .meta-info {
            width: 100%;
            margin-bottom: 20px;
            font-size: 9pt;
            border-spacing: 0;
        }

        .meta-info td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8.5pt;
            table-layout: fixed;
            /* Ensures columns respect the total width */
        }

        .data-table th {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            color: #333;
        }

        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
            word-wrap: break-word;
            /* Crucial: prevent overflow */
            overflow-wrap: break-word;
        }

        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .summary-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 15px;
            page-break-inside: avoid;
            border-radius: 4px;
        }

        .summary-title {
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 10pt;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8pt;
            border-radius: 3px;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
            text-align: right;
        }

        .page-number:before {
            content: counter(page);
        }
    </style>
</head>

<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td width="70">
                    @if (isset($setting) &&
                            $setting->church_photo_path &&
                            file_exists(public_path('storage/' . $setting->church_photo_path)))
                        <img src="{{ public_path('storage/' . $setting->church_photo_path) }}" class="logo">
                    @else
                        {{-- Fallback Logo or Empty --}}
                        <div style="width: 60px; height: 60px; background: #eee; border-radius: 4px;"></div>
                    @endif
                </td>
                <td align="left">
                    <h1 class="company-name">{{ $setting->nama_gereja ?? 'INVENTARIS APP' }}</h1>
                    <div class="company-address">{{ $setting->alamat ?? 'Alamat Organisasi belum diatur.' }}</div>
                </td>
                <td align="right" valign="top">
                    <div style="font-size: 9pt; color: #777;">Dicetak pada:</div>
                    <div style="font-weight: bold;">{{ $printedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-info">
        <tr>
            <td class="label">Laporan:</td>
            <td><strong>{{ $title }}</strong></td>
            <td class="label">Periode:</td>
            <td>{{ $filters['periode'] }}</td>
        </tr>
        <tr>
            <td class="label">Scope Lokasi:</td>
            <td>{{ !empty($filters['locations']) ? implode(', ', $filters['locations']) : 'Semua Lokasi' }}</td>
            <td class="label">Scope Kategori:</td>
            <td>{{ !empty($filters['categories']) ? implode(', ', $filters['categories']) : 'Semua Kategori' }}</td>
        </tr>
        <tr>
            <td class="label">Kondisi Barang:</td>
            <td>{{ ucfirst($filters['condition']) }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                @foreach ((array) $columns as $col)
                    <th>{{ strtoupper(str_replace(['.', '_'], ' ', $col)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    @foreach ((array) $columns as $col)
                        <td>
                            @if ($col == 'condition')
                                {{ ucfirst($item->condition) }}
                            @elseif($col == 'last_service_date')
                                {{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : '-' }}
                            @else
                                {{ data_get($item, $col) ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count((array) $columns) + 1 }}" align="center" style="padding: 20px; color: #777;">
                        Tidak ada data barang yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-title">RINGKASAN LAPORAN</div>
        <table width="100%">
            <tr>
                <td width="25%">Total Aset: <strong>{{ $summary['total'] }} Unit</strong></td>
                <td width="25%">Kondisi Baik: <strong>{{ $summary['baik'] }}</strong></td>
                <td width="25%">Kondisi Rusak: <strong>{{ $summary['rusak'] }}</strong></td>
                <td width="25%">Perbaikan: <strong>{{ $summary['perbaikan'] }}</strong></td>
            </tr>
            <tr>
                <td colspan="4" style="padding-top: 5px; font-size: 8pt; color: #666;">
                    * Total Kategori Terlibat: {{ $summary['kategori'] }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Halaman <span class="page-number"></span>
    </div>
</body>

</html>
