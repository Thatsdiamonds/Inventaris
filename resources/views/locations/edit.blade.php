<h1>Edit Location</h1>
<form action="{{ route('locations.update', $location->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div>
        <label>Name:</label>
        <input type="text" name="name" id="name" value="{{ $location->name }}" required onkeyup="syncCode()">
    </div>
    <div style="margin: 10px 0;">
        <label>
            <input type="checkbox" name="auto_code" id="auto_code" value="1" onchange="toggleCodeLock()">
            Ikuti nama lokasi (Auto-sanitize)
        </label>
    </div>
    <div>
        <label>Unique Code (4-16 chars):</label>
        <input type="text" name="unique_code" id="unique_code" value="{{ $location->unique_code }}" minlength="4"
            maxlength="16" required>
    </div>
    <div style="margin-top: 10px;">
        <label>Description:</label>
        <textarea name="description">{{ $location->description }}</textarea>
    </div>
    <br>
    <button type="submit">Update Location</button>
</form>

<script>
    function syncCode() {
        const name = document.getElementById('name').value;
        const isAuto = document.getElementById('auto_code').checked;
        if (isAuto) {
            const sanitized = name.replace(/[^A-Za-z0-9\s]/g, '')
                .replace(/\w+/g, function(w) {
                    return w[0].toUpperCase() + w.slice(1).toLowerCase();
                })
                .replace(/\s/g, '');
            document.getElementById('unique_code').value = sanitized;
        }
    }

    function toggleCodeLock() {
        const isAuto = document.getElementById('auto_code').checked;
        const codeInput = document.getElementById('unique_code');
        if (isAuto) {
            codeInput.readOnly = true;
            codeInput.style.background = "#f0f0f0";
            syncCode();
        } else {
            codeInput.readOnly = false;
            codeInput.style.background = "white";
        }
    }
    toggleCodeLock();
</script>

<br>
<a href="{{ route('locations.index') }}">Cancel</a>
