<h2 align="center">LAPORAN INVENTARIS BARANG</h2>

<ul>
    @if(!empty($filters['locations']))
        <li>Lokasi: {{ implode(', ', $filters['locations']) }}</li>
    @endif

    @if(!empty($filters['categories']))
        <li>Kategori: {{ implode(', ', $filters['categories']) }}</li>
    @endif

    <li>Kondisi: {{ ucfirst($filters['condition']) }}</li>

    <li>Periode: {{ $filters['periode'] }}</li>
</ul>

<p><strong>{{ $title }}</strong></p>
<p>Tanggal Cetak: {{ $printedAt }}</p>

<hr>

<h4>Ringkasan</h4>
<ul>
    <li>Jumlah Kategori: {{ $summary['kategori'] }}</li>
    <li>Jumlah Barang: {{ $summary['total'] }}</li>
    <li>Baik: {{ $summary['baik'] }}</li>
    <li>Rusak: {{ $summary['rusak'] }}</li>
    <li>Perbaikan: {{ $summary['perbaikan'] }}</li>
</ul>

<hr>

<table border="1" width="100%">
<tr>
@foreach((array)$columns as $col)
    <th>{{ strtoupper(str_replace(['.', '_'], ' ', $col)) }}</th>
@endforeach
</tr>

@foreach($items as $item)
<tr>
    @foreach((array)$columns as $col)
        <td>{{ data_get($item, $col) ?? '-' }}</td>
    @endforeach
</tr>
@endforeach
</table>
