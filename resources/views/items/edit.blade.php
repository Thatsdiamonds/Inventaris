<h1>Edit Item</h1>
@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php
        $isLocked = $item->condition == 'perbaikan';
    @endphp

    @if ($isLocked)
        <div style="background: #fffbe6; border: 1px solid #ffe58f; padding: 10px; margin-bottom: 20px;">
            <strong>Peringatan Keamanan:</strong> Barang ini sedang dalam proses servis. Beberapa informasi identitas
            (Tanggal Akuisisi, Kategori, Lokasi) dan Kondisi dikunci hingga servis selesai untuk menjaga integritas
            riwayat.
        </div>
    @endif

    <div>
        <label>Acquisition Date:</label>
        <input type="date" name="acquisition_date" id="acquisition_date"
            value="{{ $item->acquisition_date ? $item->acquisition_date->format('Y-m-d') : '' }}" required
            onchange="updatePreview()" {{ $isLocked ? 'disabled' : '' }}>
        @if ($isLocked)
            <input type="hidden" name="acquisition_date" value="{{ $item->acquisition_date->format('Y-m-d') }}">
        @endif
    </div>

    <div style="margin: 10px 0;">
        <label>Category:</label>
        <select name="category_id" id="category_id" required onchange="updatePreview()"
            {{ $isLocked ? 'disabled' : '' }}>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" data-code="{{ $category->unique_code }}"
                    {{ $item->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @if ($isLocked)
            <input type="hidden" name="category_id" value="{{ $item->category_id }}">
        @endif
    </div>

    <div style="margin: 10px 0;">
        <label>Location:</label>
        <select name="location_id" id="location_id" required onchange="updatePreview()"
            {{ $isLocked ? 'disabled' : '' }}>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" data-code="{{ $location->unique_code }}"
                    {{ $item->location_id == $location->id ? 'selected' : '' }}>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
        @if ($isLocked)
            <input type="hidden" name="location_id" value="{{ $item->location_id }}">
        @endif
    </div>

    <div style="margin: 20px 0; background: #e6f7ff; padding: 15px; border: 1px solid #91d5ff; border-radius: 4px;">
        <label><strong>Identitas Sistem (Unique Code):</strong></label><br>
        <input type="text" id="uqcode_preview" name="uqcode" value="{{ $item->uqcode }}" readonly
            style="width: 100%; font-family: monospace; border: none; background: transparent; font-size: 1.25em; font-weight: bold; color: #0050b3;">
        <p style="font-size: 0.85em; color: #666; margin-top: 8px;">
            *Jika Lokasi/Kategori diubah, sistem akan otomatis memperbarui kode unik & mencatat histori kode lama.
        </p>
    </div>

    <div>
        <label>Name:</label>
        <input type="text" name="name" value="{{ $item->name }}" required>
    </div>

    <div style="margin-top: 10px;">
        <label>Condition:</label>
        <select name="condition" required {{ $isLocked ? 'disabled' : '' }}>
            <option value="baik" {{ $item->condition == 'baik' ? 'selected' : '' }}>Baik</option>
            <option value="rusak" {{ $item->condition == 'rusak' ? 'selected' : '' }}>Rusak</option>
            <option value="perbaikan" {{ $item->condition == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
            <option value="dimusnahkan" {{ $item->condition == 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
        </select>
        @if ($isLocked)
            <input type="hidden" name="condition" value="perbaikan">
        @endif
    </div>

    <div style="margin-top: 10px;">
        <label>Photo:</label>
        <input type="file" name="photo">
        @if ($item->photo_path)
            <br>Current: <img src="{{ asset('storage/' . $item->photo_path) }}" width="50">
        @endif
    </div>

    <div style="margin-top: 10px;">
        <label>Service Required:</label>
        <input type="checkbox" name="service_required" id="service_required" value="1"
            {{ $item->service_required ? 'checked' : '' }} onchange="toggleInterval()">
    </div>
    <div id="interval_section"
        style="{{ $item->service_required ? 'display: block;' : 'display: none;' }} margin-top: 10px;">
        <label>Service Interval (Days):</label>
        <input type="number" name="service_interval_days" id="service_interval_days"
            value="{{ $item->service_interval_days }}" {{ $item->service_required ? 'required' : '' }}>
    </div>

    @php
        $setting = \App\Models\Setting::first();
        $autoDownloadEditDefault = $setting ? $setting->auto_download_after_edit : false;
    @endphp

    <div style="margin: 20px 0; padding: 20px; border: 2px solid #e8e8e8; border-radius: 8px; background: #fafafa;">
        <div style="display:flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <h3 style="margin:0;">QR Preview & Settings</h3>
            <a href="{{ route('reports.layout.edit', 'qr') }}" target="_blank"
                style="font-size: 0.85em; color: #1890ff;">⚙️ Atur Layout QR</a>
        </div>

        <div style="display:flex; gap: 20px; align-items: center;">
            <div id="qr_preview_container"
                style="padding:10px; background: white; border: 1px solid #ddd; min-width: 130px; min-height: 130px; display: flex; align-items: center; justify-content: center;">
                <div id="qrcode"></div>
            </div>
            <div style="flex:1;">
                <p style="font-size: 0.9em; color: #666; margin-top: 0;">Visualisasi QR real-time berdasarkan data di
                    atas.</p>
                <label
                    style="display: flex; align-items: center; gap: 8px; cursor: pointer; background: #e6f7ff; padding: 10px; border-radius: 4px; border: 1px solid #91d5ff;">
                    <input type="checkbox" name="auto_qr" id="auto_qr" value="1"
                        {{ $autoDownloadEditDefault ? 'checked' : '' }} onchange="updateButtonText()">
                    <strong>Otomatis unduh QR setelah update</strong>
                </label>
            </div>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <button type="submit" id="submit_btn"
            style="padding: 12px 25px; background: #52c41a; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em; font-weight: bold;">Update
            Item</button>
        <a href="{{ route('items.index') }}">Cancel</a>
    </div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let qrcodeInstance = null;

    function initQRCode() {
        if (!qrcodeInstance) {
            qrcodeInstance = new QRCode(document.getElementById("qrcode"), {
                width: 120,
                height: 120
            });
        }
    }

    async function updatePreview() {
        initQRCode();
        const locSelect = document.getElementById('location_id');
        const catSelect = document.getElementById('category_id');
        const dateInput = document.getElementById('acquisition_date');
        const preview = document.getElementById('uqcode_preview');
        const nameInput = document.querySelector('input[name="name"]');

        const locId = locSelect.value;
        const catId = catSelect.value;
        const date = dateInput.value;
        const name = nameInput ? nameInput.value : '';

        // Original values for lock logic
        const originalLocId = "{{ $item->location_id }}";
        const originalCatId = "{{ $item->category_id }}";
        const originalUqcode = "{{ $item->uqcode }}";

        if (locId == originalLocId && catId == originalCatId) {
            preview.value = originalUqcode;
            updateQR(originalUqcode, name, locSelect.options[locSelect.selectedIndex].text, catSelect.options[
                catSelect.selectedIndex].text);
            return;
        }

        const locCode = locSelect.options[locSelect.selectedIndex].getAttribute('data-code');
        const catCode = catSelect.options[catSelect.selectedIndex].getAttribute('data-code');
        const locName = locSelect.options[locSelect.selectedIndex].text;
        const catName = catSelect.options[catSelect.selectedIndex].text;
        const year = new Date(date).getFullYear();

        preview.value = "Menghitung...";

        try {
            const response = await fetch(
                `{{ route('api.items.next-serial') }}?location_id=${locId}&category_id=${catId}`);
            const data = await response.json();
            const uqcode = `${locCode}.${catCode}.${data.serial}.${year}`;
            preview.value = uqcode;
            updateQR(uqcode, name, locName, catName);
        } catch (error) {
            preview.value = "Error";
        }
    }

    function updateQR(uqcode, name, locName, catName) {
        initQRCode();
        const qrText = `Kode: ${uqcode}\nNama: ${name || '-'}\nLokasi: ${locName}\nKategori: ${catName}`;
        qrcodeInstance.clear();
        qrcodeInstance.makeCode(qrText);
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    function updateButtonText() {
        const cb = document.getElementById('auto_qr');
        const btn = document.getElementById('submit_btn');
        btn.innerText = cb.checked ? "Update dan Unduh QR" : "Update Item";
        btn.style.background = cb.checked ? "#52c41a" : "#1890ff";
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateButtonText();
        const nameInput = document.querySelector('input[name="name"]');
        if (nameInput) nameInput.addEventListener('input', debounce(updatePreview, 500));

        document.getElementById('location_id').addEventListener('change', debounce(updatePreview, 300));
        document.getElementById('category_id').addEventListener('change', debounce(updatePreview, 300));
        document.getElementById('acquisition_date').addEventListener('change', debounce(updatePreview, 300));

        // Initial preview
        updatePreview();
    });

    function toggleInterval() {
        const checkbox = document.getElementById('service_required');
        const section = document.getElementById('interval_section');
        const input = document.getElementById('service_interval_days');

        if (checkbox.checked) {
            section.style.display = 'block';
            input.setAttribute('required', 'required');
        } else {
            section.style.display = 'none';
            input.removeAttribute('required');
            input.value = '';
        }
    }
</script>
