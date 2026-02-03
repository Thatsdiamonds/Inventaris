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

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-done {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            <td class="label">Vendor:</td>
            <td>{{ $filters['vendor'] }}</td>
            <td class="label">Status:</td>
            <td>{{ ucfirst($filters['status']) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Vendor Servis</th>
                <th>Tgl Masuk</th>
                <th>Tgl Selesai</th>
                <th>Catatan / Masalah</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $i => $s)
                <tr>
                    <td align="center">{{ $i + 1 }}</td>
                    <td><code
                            style="background: #eee; padding: 2px 4px; border-radius: 3px;">{{ $s->item->uqcode }}</code>
                    </td>
                    <td>{{ $s->item->name }}</td>
                    <td>{{ $s->vendor }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->date_in)->format('d/m/Y') }}</td>
                    <td>
                        {{ $s->date_out ? \Carbon\Carbon::parse($s->date_out)->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $s->description }}</td>
                    <td>
                        @if ($s->finished_at)
                            <span class="status-badge status-done">Selesai</span>
                        @else
                            <span class="status-badge status-pending">Proses</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" align="center" style="padding: 20px; color: #777;">
                        Tidak ada data servis yang sesuai.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-title">RINGKASAN SERVIS</div>
        <table width="100%">
            <tr>
                <td width="33%">Total Tiket Servis: <strong>{{ $summary['total'] }}</strong></td>
                <td width="33%">Sedang Diproses: <strong>{{ $summary['proses'] }}</strong></td>
                <td width="33%">Selesai Diperbaiki: <strong>{{ $summary['selesai'] }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Halaman <span class="page-number"></span>
    </div>
</body>

</html>
