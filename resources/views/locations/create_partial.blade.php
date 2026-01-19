@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('locations.store') }}" method="POST">
    @csrf
    <div>
        <label>Name:</label>
        <input type="text" name="name" id="loc_name" required onkeyup="suggestLocCode()">
    </div>
    <div style="margin: 10px 0;">
        <label>
            <input type="checkbox" name="auto_code" id="loc_auto_code" value="1" checked
                onchange="toggleLocManualCode()">
            Ikuti nama lokasi (Auto-sanitize)
        </label>
    </div>
    <div id="loc_manual_code_section" style="display: none;">
        <label>Unique Code (4-16 chars):</label>
        <input type="text" name="unique_code" id="loc_unique_code" minlength="4" maxlength="16">
    </div>
    <div>
        <label>Description:</label>
        <textarea name="description"></textarea>
    </div>
    <button type="submit">Save</button>
    <button type="button" onclick="closeLocModal()">Cancel</button>
</form>

<script>
    function suggestLocCode() {
        const name = document.getElementById('loc_name').value;
        const auto = document.getElementById('loc_auto_code').checked;
        if (auto) {
            const sanitized = name.replace(/[^A-Za-z0-9\s]/g, '')
                .replace(/\w+/g, function(w) {
                    return w[0].toUpperCase() + w.slice(1).toLowerCase();
                })
                .replace(/\s/g, '');
            document.getElementById('loc_unique_code').value = sanitized;
        }
    }

    function toggleLocManualCode() {
        const auto = document.getElementById('loc_auto_code').checked;
        const section = document.getElementById('loc_manual_code_section');
        const input = document.getElementById('loc_unique_code');
        if (auto) {
            section.style.display = 'none';
            input.removeAttribute('required');
            suggestLocCode();
        } else {
            section.style.display = 'block';
            input.setAttribute('required', 'required');
        }
    }
    toggleLocManualCode();
</script>
