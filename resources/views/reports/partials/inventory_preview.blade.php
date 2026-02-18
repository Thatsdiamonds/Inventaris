<table class="table-preview">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Kondisi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><code>{{ $item->uqcode }}</code></td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category->name ?? '-' }}</td>
                <td>{{ $item->location->name ?? '-' }}</td>
                <td>
                    <span class="badge badge-{{ strtolower($item->condition) }}">
                        {{ $item->condition }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data yang sesuai filter</td>
            </tr>
        @endforelse
    </tbody>
</table>
