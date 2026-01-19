@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('categories.store') }}" method="POST">
    @csrf
    <div>
        <label>Name:</label>
        <input type="text" name="name" id="cat_name" required onkeyup="suggestCatCode()">
    </div>
    <div style="margin: 10px 0;">
        <label>
            <input type="checkbox" name="auto_code" id="cat_auto_code" value="1" checked
                onchange="toggleCatManualCode()">
            Ikuti nama kategori (Auto-sanitize)
        </label>
    </div>
    <div id="cat_manual_code_section" style="display: none;">
        <label>Unique Code (4-16 chars):</label>
        <input type="text" name="unique_code" id="cat_unique_code" minlength="4" maxlength="16">
    </div>
    <div>
        <label>Description:</label>
        <textarea name="description"></textarea>
    </div>
    <button type="submit">Save</button>
    <button type="button" onclick="closeCatModal()">Cancel</button>
</form>

<script>
    function suggestCatCode() {
        const name = document.getElementById('cat_name').value;
        const auto = document.getElementById('cat_auto_code').checked;
        if (auto) {
            const sanitized = name.replace(/[^A-Za-z0-9\s]/g, '')
                .replace(/\w+/g, function(w) {
                    return w[0].toUpperCase() + w.slice(1).toLowerCase();
                })
                .replace(/\s/g, '');
            document.getElementById('cat_unique_code').value = sanitized;
        }
    }

    function toggleCatManualCode() {
        const auto = document.getElementById('cat_auto_code').checked;
        const section = document.getElementById('cat_manual_code_section');
        const input = document.getElementById('cat_unique_code');
        if (auto) {
            section.style.display = 'none';
            input.removeAttribute('required');
            suggestCatCode();
        } else {
            section.style.display = 'block';
            input.setAttribute('required', 'required');
        }
    }
    toggleCatManualCode();
</script>
