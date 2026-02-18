<table class="table-preview">
    <thead>
        <tr>
            <th>No</th>
            <th>Barang</th>
            <th>Vendor</th>
            <th>Masuk</th>
            <th>Selesai</th>
            <th>Biaya</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($services as $index => $s)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $s->item->name }}</td>
                <td>{{ $s->vendor }}</td>
                <td>{{ $s->date_in }}</td>
                <td>{{ $s->date_out ?? '-' }}</td>
                <td>Rp {{ number_format($s->cost, 0, ',', '.') }}</td>
                <td>
                    @if ($s->date_out)
                        <span class="badge badge-success">Selesai</span>
                    @else
                        <span class="badge badge-warning">Proses</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">Tidak ada data yang sesuai filter</td>
            </tr>
        @endforelse
    </tbody>
</table>
