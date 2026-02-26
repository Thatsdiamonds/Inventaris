<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label QR - {{ $appName }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            -webkit-print-color-adjust: exact;
        }

        .no-print {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
            justify-content: center;
            gap: 15px;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 13.5mm 5mm;
            box-sizing: border-box;
            background: white;
            display: grid;
            grid-template-columns: 100mm 100mm;
            grid-template-rows: repeat(9, 30mm);
            gap: 0;
            page-break-after: always;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .label-image {
            width: 100mm;
            height: 30mm;
            display: block;
        }

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn btn-primary" onclick="window.print()">Cetak Sekarang</button>
        <button class="btn" onclick="window.history.back()" style="background: #64748b; color: white;">Kembali</button>
    </div>

    <div style="padding-top: 60px;">
        @php
            $itemsPerPage = 18;
            $chunks = $items->chunk($itemsPerPage);
        @endphp

        @foreach ($chunks as $pageItems)
            <div class="page">
                @foreach ($pageItems as $item)
                    @if (isset($labelImages[$item->id]))
                        <img src="data:image/jpeg;base64,{{ $labelImages[$item->id] }}" alt="Label"
                            class="label-image">
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</body>

</html>
