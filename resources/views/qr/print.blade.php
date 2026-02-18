<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label QR - {{ $appName }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        .page-container {
            background: white;
            padding: 20px;
            width: 210mm;
            /* A4 width */
            min-height: 297mm;
            /* A4 height */
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 5mm;
            justify-content: flex-start;
        }

        .label-card {
            width: 7cm;
            height: 3cm;
            border: 1px solid #000;
            box-sizing: border-box;
            display: flex;
            padding: 2mm;
            position: relative;
            background: white;
            page-break-inside: avoid;
        }

        .label-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 8pt;
            line-height: 1.2;
            overflow: hidden;
            padding-right: 2mm;
        }

        .label-qr {
            width: 26mm;
            height: 26mm;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-qr img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .org-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
            margin-bottom: 2mm;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
        }

        .item-info {
            display: flex;
            flex-direction: column;
            gap: 1mm;
        }

        .info-row {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            background: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            margin: 0 5px;
        }

        .btn:hover {
            background: #555;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .page-container {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }

            .no-print {
                display: none;
            }

            .label-card {
                border: 1px solid #000;
                /* Ensure border prints */
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="btn" onclick="window.print()">Cetak Halaman</button>
        <button class="btn" onclick="window.close()">Tutup Jendela</button>
        <p style="margin-top: 10px; color: #666; font-size: 12px;">Pastikan "Background Graphics" dicentang pada
            pengaturan cetak browser untuk hasil terbaik.</p>
    </div>

    <div class="page-container">
        <div class="labels-grid">
            @foreach ($items as $item)
                <div class="label-card">
                    <div class="label-content">
                        <div class="org-name">{{ $appName }}</div>
                        <div class="item-info">
                            <div class="info-row"><strong>Kode:</strong> {{ $item->uqcode }}</div>
                            <div class="info-row"><strong>Nama:</strong> {{ substr($item->name, 0, 20) }}</div>
                            <div class="info-row"><strong>Lokasi:</strong>
                                {{ substr($item->location->name ?? '-', 0, 15) }}</div>
                        </div>
                    </div>
                    <div class="label-qr">
                        @if (isset($qrCodes[$item->id]))
                            <img src="data:image/png;base64,{{ $qrCodes[$item->id] }}" alt="QR Code">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</body>

</html>
