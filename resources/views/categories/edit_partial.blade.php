@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('categories.update', $category->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div>
        <label>Name:</label>
        <input type="text" name="name" id="edit_cat_name" value="{{ $category->name }}" required
            onkeyup="suggestEditCatCode()">
    </div>
    <div style="margin: 10px 0;">
        <label>
            <input type="checkbox" name="auto_code" id="edit_cat_auto_code" value="1" checked
                onchange="toggleEditCatManualCode()">
            Ikuti nama kategori (Auto-sanitize)
        </label>
    </div>
    <div id="edit_cat_manual_code_section" style="display: none;">
        <label>Unique Code (4-16 chars):</label>
        <input type="text" name="unique_code" id="edit_cat_unique_code" value="{{ $category->unique_code }}"
            minlength="4" maxlength="16">
    </div>
    <div>
        <label>Description:</label>
        <textarea name="description">{{ $category->description }}</textarea>
    </div>
    <button type="submit">Update Category</button>
    <button type="button" onclick="closeEditModal()">Cancel</button>
</form>

<script>
    function suggestEditCatCode() {
        const name = document.getElementById('edit_cat_name').value;
        const auto = document.getElementById('edit_cat_auto_code').checked;
        if (auto) {
            const sanitized = name.replace(/[^A-Za-z0-9\s]/g, '')
                .replace(/\w+/g, function(w) {
                    return w[0].toUpperCase() + w.slice(1).toLowerCase();
                })
                .replace(/\s/g, '');
            document.getElementById('edit_cat_unique_code').value = sanitized;
        }
    }

    function toggleEditCatManualCode() {
        const auto = document.getElementById('edit_cat_auto_code').checked;
        const section = document.getElementById('edit_cat_manual_code_section');
        const input = document.getElementById('edit_cat_unique_code');
        if (auto) {
            section.style.display = 'none';
            input.removeAttribute('required');
            suggestEditCatCode();
        } else {
            section.style.display = 'block';
            input.setAttribute('required', 'required');
        }
    }
    toggleEditCatManualCode();
</script>
