<h1>Edit Category</h1>
<form action="{{ route('categories.update', $category->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div>
        <label>Name:</label>
        <input type="text" name="name" id="name" value="{{ $category->name }}" required onkeyup="syncCode()">
    </div>
    <div style="margin: 10px 0;">
        <label>
            <input type="checkbox" name="auto_code" id="auto_code" value="1" onchange="toggleCodeLock()">
            Ikuti nama kategori (Auto-sanitize)
        </label>
    </div>
    <div>
        <label>Unique Code (4-16 chars):</label>
        <input type="text" name="unique_code" id="unique_code" value="{{ $category->unique_code }}" minlength="4"
            maxlength="16" required oninput="checkCodeChange()">

        <div id="code_warning"
            style="display:none; margin-top:10px; padding:10px; background:#fffbe6; border:1px solid #ffe58f; color:#856404; font-size:0.9em;">
            ⚠️ <strong>Perhatian:</strong> Anda mengubah Kode Unik. Perubahan ini akan memperbarui
            <strong>{{ $itemCount }} barang</strong> yang terhubung.
            @if ($itemCount > 50)
                Karena jumlah barang cukup banyak, pembaruan akan diproses di latar belakang.
            @endif
            <div style="margin-top: 5px;">
                <button type="button" onclick="loadItemsReview()" id="btn_review"
                    style="font-size: 0.85em; padding: 2px 8px;">Tinjau & Pilih Barang</button>
            </div>
            <div id="items_review_list"
                style="display: none; margin-top: 10px; background: white; border: 1px solid #ddd; max-height: 250px; overflow-y: auto; padding: 8px; color: #333;">
                <p style="margin: 0 0 5px 0; font-weight: bold; font-size: 0.85em;">Pilih barang yang ingin diupdate:
                </p>
                <div id="review_spinner" style="display: none;">Memuat barang...</div>
                <div id="items_checkboxes"></div>
            </div>
        </div>
    </div>
    <div style="margin-top: 10px;">
        <label>Description:</label>
        <textarea name="description">{{ $category->description }}</textarea>
    </div>
    <br>
    <button type="submit">Update Category</button>
</form>

<script>
    const originalCode = "{{ $category->unique_code }}";
    const itemCount = {{ $itemCount }};
    const catId = "{{ $category->id }}";

    function loadItemsReview() {
        const container = document.getElementById('items_review_list');
        const checkboxes = document.getElementById('items_checkboxes');
        const spinner = document.getElementById('review_spinner');

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
                            <input type="checkbox" name="selected_items_proxy" value="${item.id}" checked onchange="updateExclusions()">
                            ${item.uqcode} - ${item.name}
                        </label>
                    `;
                    checkboxes.appendChild(div);
                });
                updateExclusions();
            });
    }

    function updateExclusions() {
        const form = document.querySelector('#items_checkboxes').closest('form');
        form.querySelectorAll('input[name="excluded_items[]"]').forEach(el => el.remove());

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
        checkCodeChange();
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
        checkCodeChange();
    }

    function checkCodeChange() {
        const currentCode = document.getElementById('unique_code').value;
        const warning = document.getElementById('code_warning');
        if (itemCount > 0 && currentCode !== originalCode && currentCode.length >= 4) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }

    // Init status
    toggleCodeLock();
</script>

<br>
<a href="{{ route('categories.index') }}">Cancel</a>
