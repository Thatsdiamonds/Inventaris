<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .label-container {
            display: inline-block;
            margin: 2mm;
            page-break-inside: avoid;
        }

        .label-card {
            border: 0.5px solid #000;
            padding: 3mm;
            display: table;
            width: 80mm;
            height: 40mm;
            overflow: hidden;
            box-sizing: border-box;
        }

        .left-section {
            display: table-cell;
            vertical-align: middle;
            padding-right: 3mm;
        }

        .right-section {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30mm;
        }

        .info-row {
            margin-bottom: 1mm;
            font-size: 9pt;
            white-space: nowrap;
        }

        .church-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2mm;
        }

        .qr-img {
            width: 30mm;
            height: 30mm;
        }

        /* Custom CSS from database */
        {!! $customCss !!}
    </style>
</head>

<body>
    @foreach ($items as $item)
        <div class="label-container">
            <div class="label-card">
                @php
                    $hasQr = in_array('qr_code', $columns);
                    $leftFields = array_filter($columns, fn($c) => $c !== 'qr_code');
                @endphp

                <div class="left-section">
                    @foreach ($leftFields as $col)
                        <div class="field-{{ str_replace('.', '-', $col) }}">
                            @if ($col == 'church_name')
                                <div class="church-name">{{ $appName }}</div>
                            @elseif($col == 'location.name')
                                <div class="info-row"><span class="label">Ruang</span> :
                                    {{ $item->location->name ?? '-' }}</div>
                            @elseif($col == 'uqcode')
                                <div class="info-row"><span class="label">Kode Barang</span> : {{ $item->uqcode }}</div>
                            @elseif($col == 'created_at')
                                <div class="info-row"><span class="label">Tahun Inventaris</span> :
                                    {{ $item->created_at ? $item->created_at->format('Y') : '-' }}</div>
                            @elseif($col == 'acquisition_date')
                                <div class="info-row"><span class="label">Tahun Perolehan</span> :
                                    {{ $item->acquisition_date ? $item->acquisition_date->format('Y') : '-' }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($hasQr)
                    <div class="right-section">
                        <img src="data:image/png;base64,{{ $qrCodes[$item->id] }}" class="qr-img">
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</body>

</html>
