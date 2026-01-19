<h1>Pencatatan Servis Barang</h1>

<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>Barang:</strong> {{ $item->name }}<br>
    <strong>Kode:</strong> {{ $item->uqcode }}
</div>

<form action="{{ route('items.service.confirm', $item->id) }}" method="POST">
    @csrf
    <div>
        <label>Vendor / Teknisi:</label><br>
        <input type="text" name="vendor" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label>Tipe Servis:</label><br>
        <select name="service_type" style="width: 300px;">
            <option value="routine">Routine Maintenance (Mereset jadwal tempo)</option>
            <option value="manual">Manual Service (Perbaikan tanpa mereset jadwal)</option>
        </select>
        <p style="font-size: 0.85em; color: #666; margin-top: 5px;">
            *Pilih <strong>Routine</strong> jika ini adalah bagian dari perawatan berkala barang.<br>
            *Pilih <strong>Manual</strong> jika perbaikan dilakukan di luar jadwal (insidental).
        </p>
    </div>
    <br>
    <div>
        <label>Tanggal Masuk Servis:</label><br>
        <input type="date" name="date_in" value="{{ date('Y-m-d') }}" required>
    </div>
    <br>
    <div>
        <label>Keterangan / Kerusakan:</label><br>
        <textarea name="description" required style="width: 300px; height: 100px;"></textarea>
    </div>
    <br>
    <button type="submit">Lanjut ke Konfirmasi</button>
    <a href="{{ route('items.index') }}">Batal</a>
</form>
