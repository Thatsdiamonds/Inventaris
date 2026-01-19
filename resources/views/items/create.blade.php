<h1>Create Item</h1>
@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label>Acquisition Date:</label>
        <input type="date" name="acquisition_date" id="acquisition_date" value="{{ date('Y-m-d') }}" required
            onchange="updatePreview()">
    </div>

    <div style="margin: 10px 0;">
        <label>Category:</label>
        <select name="category_id" id="category_id" required onchange="updatePreview()">
            <option value="">-- Select Category --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" data-code="{{ $category->unique_code }}">{{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="margin: 10px 0;">
        <label>Location:</label>
        <select name="location_id" id="location_id" required onchange="updatePreview()">
            <option value="">-- Select Location --</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" data-code="{{ $location->unique_code }}">{{ $location->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="margin: 20px 0; background: #f0f0f0; padding: 10px; border-radius: 4px;">
        <label><strong>Unique Code Identity:</strong></label><br>
        <input type="text" id="uqcode_preview" name="uqcode_preview" readonly
            style="width: 100%; font-family: monospace; border: none; background: transparent; font-size: 1.2em; font-weight: bold; color: #333;">
        <p style="font-size: 0.8em; color: #666; margin-top: 5px;">*Format: Lokasi.Kategori.Nomor.Tahun (Otomatis &
            Readonly)</p>
    </div>

    <div>
        <label>Name:</label>
        <input type="text" name="name" required>
    </div>

    <div style="margin-top: 10px;">
        <label>Condition:</label>
        <select name="condition" required>
            <option value="baik">Baik</option>
            <option value="rusak">Rusak</option>
            <option value="perbaikan">Perbaikan</option>
            <option value="dimusnahkan">Dimusnahkan</option>
        </select>
    </div>

    <div style="margin-top: 10px;">
        <label>Photo:</label>
        <input type="file" name="photo">
    </div>

    <div style="margin-top: 10px;">
        <label>Service Required:</label>
        <input type="checkbox" name="service_required" id="service_required" value="1" onchange="toggleInterval()">
    </div>
    <div id="interval_section" style="display: none; margin-top: 10px;">
        <label>Service Interval (Days):</label>
        <input type="number" name="service_interval_days" id="service_interval_days">
    </div>

    <div style="margin-top: 20px;">
        <button type="submit">Save Item</button>
        <a href="{{ route('items.index') }}">Cancel</a>
    </div>
</form>

<script>
    async function updatePreview() {
        const locSelect = document.getElementById('location_id');
        const catSelect = document.getElementById('category_id');
        const dateInput = document.getElementById('acquisition_date');
        const preview = document.getElementById('uqcode_preview');

        const locId = locSelect.value;
        const catId = catSelect.value;
        const date = dateInput.value;

        if (!locId || !catId || !date) {
            preview.value = "Menunggu pilihan Loc/Cat/Date...";
            return;
        }

        const locCode = locSelect.options[locSelect.selectedIndex].getAttribute('data-code');
        const catCode = catSelect.options[catSelect.selectedIndex].getAttribute('data-code');
        const year = new Date(date).getFullYear();

        preview.value = "Menghitung serial...";

        try {
            const response = await fetch(
                `{{ route('api.items.next-serial') }}?location_id=${locId}&category_id=${catId}`);
            const data = await response.json();
            preview.value = `${locCode}.${catCode}.${data.serial}.${year}`;
        } catch (error) {
            preview.value = "Error fetching serial";
        }
    }

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
