<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #eee; }
    </style>
</head>
<body>

<h3 align="center">{{ strtoupper($title) }}</h3>
<p align="center">Dicetak: {{ $printedAt }}</p>

<p>
Vendor: {{ $filters['vendor'] }} <br>
Periode: {{ $filters['periode'] }} <br>
Status: {{ ucfirst($filters['status']) }}
</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Vendor</th>
            <th>Tgl Masuk</th>
            <th>Tgl Selesai</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($services as $i => $s)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $s->item->uqcode }}</td>
            <td>{{ $s->item->name }}</td>
            <td>{{ $s->vendor }}</td>
            <td>{{ $s->date_in }}</td>
            <td>{{ $s->date_out ?? '-' }}</td>
            <td>{{ $s->description }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p>
<b>Ringkasan:</b><br>
Total Servis: {{ $summary['total'] }}<br>
Dalam Proses: {{ $summary['proses'] }}<br>
Selesai: {{ $summary['selesai'] }}
</p>

</body>
</html>
