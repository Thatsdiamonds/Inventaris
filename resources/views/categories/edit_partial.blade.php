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
    <div id="edit_cat_warning"
        style="display: none; background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeeba; font-size: 0.9em;">
        <strong>Peringatan!</strong> Mengubah Kode Unik akan mengubah <strong>{{ $itemCount }}</strong> UQCode barang
        yang terkait.
        @if ($itemCount > 50)
            <br>Proses ini akan berjalan di latar belakang karena jumlah barang cukup banyak.
        @endif
        <div style="margin-top: 5px;">
            <button type="button" onclick="loadCatItemsReview()" id="btn_review_cat"
                style="font-size: 0.85em; padding: 2px 8px;">Tinjau & Pilih Barang</button>
        </div>
        <div id="cat_items_review_list"
            style="display: none; margin-top: 10px; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; padding: 5px; color: #333;">
            <p style="margin: 0 0 5px 0; font-weight: bold; font-size: 0.85em;">Pilih barang yang ingin diupdate:</p>
            <div id="cat_review_spinner" style="display: none;">Memuat barang...</div>
            <div id="cat_items_checkboxes"></div>
        </div>
    </div>

    <div>
        <label>Description:</label>
        <textarea name="description">{{ $category->description }}</textarea>
    </div>
    <button type="submit">Update Category</button>
    <button type="button" onclick="closeEditModal()">Cancel</button>
</form>

<script>
    const originalCatCode = "{{ $category->unique_code }}";
    const itemCount = {{ $itemCount }};
    const catId = "{{ $category->id }}";

    function loadCatItemsReview() {
        const container = document.getElementById('cat_items_review_list');
        const checkboxes = document.getElementById('cat_items_checkboxes');
        const spinner = document.getElementById('cat_review_spinner');

        if (container.style.display === 'block') {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        checkboxes.innerHTML = '';
        spinner.style.display = 'block';

        fetch(`/categories/${catId}/items`)
            .then(response => response.json())
            .then(items => {
                spinner.style.display = 'none';
                items.forEach(item => {
                    const div = document.createElement('div');
                    div.style.fontSize = '0.85em';
                    div.style.marginBottom = '2px';
                    div.innerHTML = `
                        <label>
                            <input type="checkbox" name="selected_items_proxy" value="${item.id}" checked onchange="updateCatExclusions()">
                            ${item.uqcode} - ${item.name}
                        </label>
                    `;
                    checkboxes.appendChild(div);
                });
                updateCatExclusions();
            });
    }

    function updateCatExclusions() {
        // Remove old hidden inputs
        const form = document.querySelector('#cat_items_checkboxes').closest('form');
        form.querySelectorAll('input[name="excluded_items[]"]').forEach(el => el.remove());

        // Add new ones for unchecked
        const proxies = document.querySelectorAll('input[name="selected_items_proxy"]');
        proxies.forEach(p => {
            if (!p.checked) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'excluded_items[]';
                hidden.value = p.value;
                form.appendChild(hidden);
            }
        });
    }

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
        checkCatCodeChange();
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
        checkCatCodeChange();
    }

    function checkCatCodeChange() {
        const currentCode = document.getElementById('edit_cat_unique_code').value;
        const warning = document.getElementById('edit_cat_warning');
        if (itemCount > 0 && currentCode !== originalCatCode && currentCode.length >= 4) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }

    // Add event listener for manual code changes
    document.getElementById('edit_cat_unique_code').addEventListener('input', checkCatCodeChange);

    toggleEditCatManualCode();
</script>
