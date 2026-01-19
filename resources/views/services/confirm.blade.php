<h1>Konfirmasi Riwayat Servis</h1>
<p>Mohon periksa kembali data servis berikut sebelum disimpan ke sistem.</p>

<div style="background: #fffbe6; padding: 20px; border: 1px solid #ffe58f; border-radius: 5px; max-width: 500px;">
    <h3 style="margin-top: 0;">Ringkasan Servis</h3>
    <table cellpadding="5">
        <tr>
            <td><strong>Nama Barang</strong></td>
            <td>: {{ $item->name }}</td>
        </tr>
        <tr>
            <td><strong>Unique Code</strong></td>
            <td>: {{ $item->uqcode }}</td>
        </tr>
        <tr>
            <td><strong>Tipe Servis</strong></td>
            <td style="text-transform: capitalize;">: {{ $service_type }}</td>
        </tr>
        <tr>
            <td><strong>Vendor</strong></td>
            <td>: {{ $vendor }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>: {{ \Carbon\Carbon::parse($date_in)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Keterangan</strong></td>
            <td>: {{ $description }}</td>
        </tr>
    </table>
</div>

<div style="margin-top: 20px;">
    <form action="{{ route('items.service.store', $item->id) }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="vendor" value="{{ $vendor }}">
        <input type="hidden" name="date_in" value="{{ $date_in }}">
        <input type="hidden" name="description" value="{{ $description }}">
        <input type="hidden" name="service_type" value="{{ $service_type }}">

        <button type="submit"
            style="background: #52c41a; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
            Konfirmasi & Simpan Data
        </button>
    </form>

    <a href="{{ route('items.service.create', $item->id) }}"
        style="margin-left: 10px; color: #666; text-decoration: none;">
        Kembali / Ubah Data
    </a>
</div>

<p style="color: #666; font-size: 0.9em; margin-top: 20px;">
    *Setelah dikonfirmasi, status barang akan otomatis berubah menjadi <strong>"Perbaikan"</strong>.
    @if ($service_type == 'routine')
        <br>*Jadwal maintenance berkala akan <strong>direset</strong> berdasarkan tanggal ini.
    @else
        <br>*Daftar ini dicatat sebagai servis manual, jadwal maintenance <strong>tidak akan berubah</strong>.
    @endif
</p>
