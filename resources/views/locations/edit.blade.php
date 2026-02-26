@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Edit Lokasi</h1>
        <p class="text-secondary">Pembaruan informasi lokasi: <strong>{{ $location->name }}</strong></p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="mb-0" style="padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 650px;">
        <form action="{{ route('locations.update', $location->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Lokasi <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $location->name) }}" required
                    onkeyup="syncCode()">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="auto_code" id="auto_code" value="1"
                        {{ old('auto_code') ? 'checked' : '' }} onchange="toggleCodeLock()">
                    <span style="font-size: 0.9rem;">Generate kode unik otomatis dari nama</span>
                </label>
            </div>

            <div class="form-group">
                <label>Kode Unik (2-6 karakter) <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="unique_code" id="unique_code"
                    value="{{ old('unique_code', $location->unique_code) }}" minlength="2" maxlength="6" required
                    oninput="checkCodeChange()">

                <div id="code_warning" class="alert alert-warning py-3 mt-3 mb-0"
                    style="display:none; font-size:0.875rem; border-left: 4px solid var(--color-warning);">
                    <div style="font-weight: 700; margin-bottom: 0.25rem; color: var(--color-primary);">
                        <svg class="icon icon-sm">
                            <use href="#icon-alert"></use>
                        </svg>
                        Peringatan Perubahan Lokasi
                    </div>
                    Anda mengubah Kode Unik Lokasi. Perubahan ini akan memperbarui <strong>{{ $itemCount }}
                        barang</strong> yang terhubung.

                    @if ($itemCount > 50)
                        <p class="mt-2 mb-0" style="font-size: 0.8rem;"><i>*Pembaruan akan diproses di latar belakang karena
                                jumlah data besar.</i></p>
                    @endif

                    <div class="mt-3">
                        <button type="button" onclick="loadItemsReview()" id="btn_review" class="btn btn-ghost btn-sm"
                            style="background: white; border: 1px solid var(--color-border);">
                            Tinjau & Pilih Barang
                        </button>
                    </div>

                    <div id="items_review_list"
                        style="display: none; margin-top: 1rem; background: white; border: 1px solid var(--color-border-light); border-radius: var(--radius-md); max-height: 200px; overflow-y: auto; padding: 0.75rem;">
                        <p style="margin-bottom: 0.5rem; font-weight: 700; font-size: 0.8rem;">Pilih barang yang ingin
                            diupdate:</p>
                        <div id="review_spinner" style="display: none; font-size: 0.8rem;" class="text-muted">Memuat data
                            barang...</div>
                        <div id="items_checkboxes" style="display: flex; flex-direction: column; gap: 0.25rem;"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" placeholder="Penjelasan singkat lokasi ini...">{{ old('description', $location->description) }}</textarea>
            </div>

            <div class="flex gap-2 mt-4"
                style="border-top: 1px solid var(--color-border-light); padding-top: var(--spacing-lg);">
                <button type="submit" class="btn btn-primary">Update Lokasi</button>
                <a href="{{ route('locations.index') }}" wire:navigate class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>

    <script>
        const originalCode = "{{ $location->unique_code }}";
        const itemCount = {{ $itemCount }};
        const locId = "{{ $location->id }}";

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

            fetch(`/locations/${locId}/items`)
                .then(response => response.json())
                .then(items => {
                    spinner.style.display = 'none';
                    items.forEach(item => {
                        const div = document.createElement('div');
                        div.style.fontSize = '0.8rem';
                        div.innerHTML = `
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.25rem; border-radius: 4px;">
                            <input type="checkbox" name="selected_items_proxy" value="${item.id}" checked onchange="updateExclusions()">
                            <span><strong>${item.uqcode}</strong> - ${item.name}</span>
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
                document.getElementById('unique_code').value = sanitized.substring(0, 6);
            }
            checkCodeChange();
        }

        function toggleCodeLock() {
            const isAuto = document.getElementById('auto_code').checked;
            const codeInput = document.getElementById('unique_code');
            if (isAuto) {
                codeInput.readOnly = true;
                codeInput.style.background = "var(--color-bg-tertiary)";
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
            if (itemCount > 0 && currentCode !== originalCode && currentCode.length >= 2) {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleCodeLock);
    </script>
@endsection
