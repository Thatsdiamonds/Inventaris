<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 5mm;
            size: A4;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .label-container {
            display: inline-block;
            margin: 1.5mm;
            page-break-inside: avoid;
            vertical-align: top;
        }

        .label-card {
            border: 0.2mm solid #000;
            display: block;
            width: 100mm;
            height: 30mm;
            overflow: hidden;
            box-sizing: border-box;
            position: relative;
        }

        .left-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 70mm;
            height: 30mm;
            padding: 2mm 3mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-section {
            position: absolute;
            right: 0;
            top: 0;
            width: 30mm;
            height: 30mm;
            border-left: 0.1mm solid #000;
            box-sizing: border-box;
        }

        .info-row {
            margin-bottom: 0.5mm;
            font-size: 7pt;
            font-weight: bold;
            line-height: 1.1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .church-name {
            font-weight: bold;
            font-size: 8.5pt;
            margin-bottom: 1.5mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border-bottom: 0.1mm solid #eee;
            padding-bottom: 0.5mm;
        }

        .qr-img {
            width: 29.8mm;
            height: 29.8mm;
            display: block;
        }

        .caps {
            text-transform: uppercase;
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
                                <div class="info-row">LOKASI : <span
                                        class="caps">{{ $item->location->name ?? '-' }}</span></div>
                            @elseif($col == 'uqcode')
                                <div class="info-row">KODE : {{ $item->uqcode }}</div>
                            @elseif($col == 'created_at')
                                <div class="info-row">INV :
                                    {{ $item->created_at ? $item->created_at->format('Y') : '-' }}</div>
                            @elseif($col == 'acquisition_date')
                                <div class="info-row">BELI :
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
