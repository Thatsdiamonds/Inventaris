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

    <div>
        <label>Acquisition Date:</label>
        <input type="date" name="acquisition_date" id="edit_acquisition_date"
            value="{{ $item->acquisition_date ? $item->acquisition_date->format('Y-m-d') : '' }}" required
            onchange="updateEditPreview()">
    </div>

    <div style="margin: 10px 0;">
        <label>Category:</label>
        <select name="category_id" id="edit_category_id" required onchange="updateEditPreview()">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" data-code="{{ $category->unique_code }}"
                    {{ $item->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="margin: 10px 0;">
        <label>Location:</label>
        <select name="location_id" id="edit_location_id" required onchange="updateEditPreview()">
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" data-code="{{ $location->unique_code }}"
                    {{ $item->location_id == $location->id ? 'selected' : '' }}>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="margin: 20px 0; background: #e6f7ff; padding: 15px; border: 1px solid #91d5ff; border-radius: 4px;">
        <label><strong>Identitas Sistem (Unique Code):</strong></label><br>
        <input type="text" id="edit_uqcode_preview" name="uqcode" value="{{ $item->uqcode }}" readonly
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
        <select name="condition" required>
            <option value="baik" {{ $item->condition == 'baik' ? 'selected' : '' }}>Baik</option>
            <option value="rusak" {{ $item->condition == 'rusak' ? 'selected' : '' }}>Rusak</option>
            <option value="perbaikan" {{ $item->condition == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
            <option value="dimusnahkan" {{ $item->condition == 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
        </select>
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
        <input type="checkbox" name="service_required" id="edit_service_required" value="1"
            {{ $item->service_required ? 'checked' : '' }} onchange="toggleEditInterval()">
    </div>
    <div id="edit_interval_section"
        style="{{ $item->service_required ? 'display: block;' : 'display: none;' }} margin-top: 10px;">
        <label>Service Interval (Days):</label>
        <input type="number" name="service_interval_days" id="edit_service_interval_days"
            value="{{ $item->service_interval_days }}" {{ $item->service_required ? 'required' : '' }}>
    </div>

    <div style="margin-top: 20px;">
        <button type="submit">Update Item</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
    </div>
</form>

<script>
    async function updateEditPreview() {
        const locSelect = document.getElementById('edit_location_id');
        const catSelect = document.getElementById('edit_category_id');
        const dateInput = document.getElementById('edit_acquisition_date');
        const preview = document.getElementById('edit_uqcode_preview');

        const locId = locSelect.value;
        const catId = catSelect.value;
        const date = dateInput.value;

        const originalLocId = "{{ $item->location_id }}";
        const originalCatId = "{{ $item->category_id }}";
        const originalUqcode = "{{ $item->uqcode }}";

        if (locId == originalLocId && catId == originalCatId) {
            preview.value = originalUqcode;
            return;
        }

        const locCode = locSelect.options[locSelect.selectedIndex].getAttribute('data-code');
        const catCode = catSelect.options[catSelect.selectedIndex].getAttribute('data-code');
        const year = new Date(date).getFullYear();

        preview.value = "Menghitung...";

        try {
            const response = await fetch(
                `{{ route('api.items.next-serial') }}?location_id=${locId}&category_id=${catId}`);
            const data = await response.json();
            preview.value = `${locCode}.${catCode}.${data.serial}.${year}`;
        } catch (error) {
            preview.value = "Error";
        }
    }

    function toggleEditInterval() {
        const checkbox = document.getElementById('edit_service_required');
        const section = document.getElementById('edit_interval_section');
        const input = document.getElementById('edit_service_interval_days');

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
