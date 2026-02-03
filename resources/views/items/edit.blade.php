@extends('layouts.app')

@section('content')
    @php
        $isLocked = $item->condition == 'perbaikan';
    @endphp

    <div class="page-header mb-4 flex-between">
        <div>
            <h1 class="mb-0">Edit Barang</h1>
            <p class="text-secondary">Pembaruan informasi aset #{{ $item->id }}</p>
        </div>
        @if ($isLocked)
            <span class="badge badge-warning" style="padding: 0.5rem 1rem;">
                <svg class="icon icon-sm">
                    <use href="#icon-alert"></use>
                </svg>
                Status: Dalam Servis (Terkunci)
            </span>
        @endif
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

    <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: var(--spacing-xl); align-items: start;">

            <!-- Left Side: Form Data -->
            <div class="card">
                @if ($isLocked)
                    <div class="alert alert-info py-2 mb-4" style="font-size: 0.85rem;">
                        Identitas barang (Tanggal, Kategori, Lokasi) dikunci selama masa perbaikan dilakukan.
                    </div>
                @endif

                <h3 class="mb-3"
                    style="font-size: 1.1rem; border-bottom: 1px solid var(--color-border-light); padding-bottom: 0.5rem;">
                    Informasi Dasar</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label>Nama Barang <span style="color: var(--color-danger);">*</span></label>
                        <input type="text" name="name" id="item_name" value="{{ old('name', $item->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Akuisisi <span style="color: var(--color-danger);">*</span></label>
                        <input type="date" name="acquisition_date" id="acquisition_date"
                            value="{{ old('acquisition_date', $item->acquisition_date ? $item->acquisition_date->format('Y-m-d') : '') }}"
                            required {{ $isLocked ? 'disabled' : '' }}>
                        @if ($isLocked)
                            <input type="hidden" name="acquisition_date"
                                value="{{ $item->acquisition_date->format('Y-m-d') }}">
                        @endif
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label>Kategori <span style="color: var(--color-danger);">*</span></label>
                        <select name="category_id" id="category_id" required {{ $isLocked ? 'disabled' : '' }}>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-code="{{ $category->unique_code }}"
                                    {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}</option>
                            @endforeach
                        </select>
                        @if ($isLocked)
                            <input type="hidden" name="category_id" value="{{ $item->category_id }}">
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Lokasi <span style="color: var(--color-danger);">*</span></label>
                        <select name="location_id" id="location_id" required {{ $isLocked ? 'disabled' : '' }}>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" data-code="{{ $location->unique_code }}"
                                    {{ old('location_id', $item->location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}</option>
                            @endforeach
                        </select>
                        @if ($isLocked)
                            <input type="hidden" name="location_id" value="{{ $item->location_id }}">
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label>Kondisi <span style="color: var(--color-danger);">*</span></label>
                    <select name="condition" id="item_condition" required {{ $isLocked ? 'disabled' : '' }}>
                        <option value="baik" {{ old('condition', $item->condition) == 'baik' ? 'selected' : '' }}>Baik
                        </option>
                        <option value="rusak" {{ old('condition', $item->condition) == 'rusak' ? 'selected' : '' }}>Rusak
                        </option>
                        <option value="perbaikan"
                            {{ old('condition', $item->condition) == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                        <option value="dimusnahkan"
                            {{ old('condition', $item->condition) == 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan
                        </option>
                    </select>
                    @if ($isLocked)
                        <input type="hidden" name="condition" value="perbaikan">
                    @endif
                </div>

                <div class="form-group">
                    <label>Ubah Foto Barang</label>
                    <div id="photo_preview_container" style="display: flex; align-items: flex-start; gap: 1rem;">
                        <!-- Preview Box -->
                        <div id="photo_preview_box"
                            style="width: 80px; height: 80px; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0; position: relative; border: 2px solid var(--color-border); {{ $item->photo_path ? '' : 'display: none;' }}">
                            <img id="photo_preview_img"
                                src="{{ $item->photo_path ? asset('storage/' . $item->photo_path) : '' }}"
                                data-original="{{ $item->photo_path ? asset('storage/' . $item->photo_path) : '' }}"
                                loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" id="photo_clear_btn" onclick="clearPhotoPreview()"
                                style="position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; background: var(--color-danger); color: white; border: 2px solid white; cursor: pointer; display: {{ $item->photo_path ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-size: 14px; line-height: 1; padding: 0;">
                                ×
                            </button>
                        </div>

                        <!-- Placeholder when no image at all -->
                        <div id="photo_placeholder"
                            style="width: 80px; height: 80px; background: var(--color-bg-secondary); border-radius: var(--radius-md); border: 2px dashed var(--color-border); display: {{ $item->photo_path ? 'none' : 'flex' }}; align-items: center; justify-content: center; color: var(--color-text-muted); flex-shrink: 0;">
                            <svg class="icon icon-lg">
                                <use href="#icon-image"></use>
                            </svg>
                        </div>

                        <!-- File Input -->
                        <div style="flex: 1;">
                            <input type="file" name="photo" id="photo_input" accept="image/*"
                                style="border: 1px dashed var(--color-border); padding: 0.5rem; width: 100%;"
                                onchange="previewPhoto(this)">
                            <small class="text-muted" style="display: block; margin-top: 0.25rem;">Pilih file baru untuk
                                mengganti foto saat ini.</small>
                            <button type="button" id="revert_photo_btn" onclick="revertToOriginal()"
                                style="display: none; margin-top: 0.5rem; font-size: 0.75rem; color: var(--color-accent); background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline;">
                                Batalkan perubahan foto
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    style="background: var(--color-bg-secondary); padding: var(--spacing-md); border-radius: var(--radius-md); border-left: 4px solid var(--color-accent);">
                    <label class="mb-1" style="font-weight: 700; color: var(--color-primary);">KODE BARANG</label>
                    <input type="text" id="uqcode_preview" name="uqcode" readonly
                        style="border: none; background: transparent; padding: 0; font-family: monospace; font-size: 1.25rem; font-weight: 700; color: var(--color-accent);"
                        value="{{ $item->uqcode }}">
                    <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">*Diperbarui otomatis jika
                        Kategori atau Lokasi berubah.</p>
                </div>

                <div class="mt-4"
                    style="border-top: 1px solid var(--color-border-light); padding-top: var(--spacing-lg);">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="service_required" id="service_required" value="1"
                            {{ old('service_required', $item->service_required) ? 'checked' : '' }}
                            style="width: 1.2rem; height: 1.2rem;">
                        <div>
                            <strong style="display: block; font-size: 0.9375rem;">Aktifkan Jadwal Perawatan</strong>
                            <span class="text-muted" style="font-size: 0.8rem;">Atur jangka waktu servis rutin.</span>
                        </div>
                    </label>

                    <div id="interval_section"
                        style="{{ old('service_required', $item->service_required) ? 'display: block;' : 'display: none;' }} margin-top: var(--spacing-md); padding-left: 2rem;">
                        <label style="font-size: 0.875rem;">Interval Servis (Hari)</label>
                        <input type="number" name="service_interval_days" id="service_interval_days"
                            placeholder="Contoh: 90" style="max-width: 150px;"
                            value="{{ old('service_interval_days', $item->service_interval_days) }}">
                    </div>
                </div>
            </div>

            <!-- Right Side: QR Preview & Submit -->
            <div style="position: sticky; top: 2rem; display: flex; flex-direction: column; gap: var(--spacing-lg);">
                <div class="card" style="text-align: center;">
                    <h3 class="mb-3" style="font-size: 1rem;">Preview Label QR</h3>
                    <div
                        style="background: white; border: 1px solid var(--color-border); padding: 1rem; border-radius: var(--radius-md); display: inline-block; margin-bottom: 1rem; position: relative; width: 212px; height: 212px; box-sizing: border-box;">
                        <div id="qrcode" style="width: 180px; height: 180px;"></div>
                        <div id="qr_placeholder"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: white; border-radius: var(--radius-md); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box;">
                            <svg class="icon" style="width: 48px; height: 48px; color: #ddd; margin-bottom: 0.5rem;">
                                <use href="#icon-qr-code"></use>
                            </svg>
                            <span style="font-size: 0.7rem; color: #999; line-height: 1.3;">Lengkapi Nama, Lokasi, &
                                Kategori</span>
                        </div>
                    </div>
                    <p class="text-muted" style="font-size: 0.75rem; line-height: 1.4;">QR diperbarui otomatis sesuai
                        perubahan data.</p>

                    @php
                        $setting = \App\Models\Setting::first();
                        $autoDownload = $setting ? $setting->auto_download_after_edit : false;
                    @endphp

                    <label
                        style="display: flex; align-items: flex-start; gap: 0.5rem; text-align: left; background: var(--color-bg-tertiary); padding: 0.75rem; border-radius: var(--radius-md); cursor: pointer; border: 1px solid var(--color-border-light);">
                        <input type="checkbox" name="auto_qr" id="auto_qr" value="1"
                            {{ $autoDownload ? 'checked' : '' }} style="margin-top: 2px;">
                        <span style="font-size: 0.8rem; font-weight: 500;">Unduh QR otomatis setelah menyimpan data</span>
                    </label>
                </div>

                <button type="submit" id="submit_btn" class="btn btn-primary btn-lg"
                    style="width: 100%; font-weight: 700;">Update Barang</button>
                <a href="{{ route('items.index') }}" wire:navigate class="btn btn-ghost"
                    style="width: 100%; text-align: center; justify-content: center;">Batal</a>
            </div>
        </div>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function() {
            let qrcodeInstance = null;
            let previewTimeout = null;
            let initRetryCount = 0;
            const maxRetries = 10;

            function waitForQRCodeLibrary(callback) {
                if (typeof QRCode !== 'undefined') {
                    callback();
                } else if (initRetryCount < maxRetries) {
                    initRetryCount++;
                    setTimeout(() => waitForQRCodeLibrary(callback), 100);
                } else {
                    console.warn('QRCode library failed to load after retries');
                }
            }

            function initQRCode() {
                const container = document.getElementById("qrcode");
                if (!container) return null;
                container.innerHTML = "";
                if (typeof QRCode === 'undefined') return null;
                return new QRCode(container, {
                    width: 180,
                    height: 180,
                    colorDark: "#2c3e50",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }

            function renderQR(uqcode, name, locName, catName) {
                // Reset instance to force re-creation
                qrcodeInstance = initQRCode();
                if (qrcodeInstance) {
                    const qrText = `Kode: ${uqcode}\nNama: ${name || '-'}\nLokasi: ${locName}\nKategori: ${catName}`;
                    qrcodeInstance.clear();
                    qrcodeInstance.makeCode(qrText);
                }
            }

            async function updatePreview() {
                const locSelect = document.getElementById('location_id');
                const catSelect = document.getElementById('category_id');
                const dateInput = document.getElementById('acquisition_date');
                const previewEl = document.getElementById('uqcode_preview');
                const nameInput = document.getElementById('item_name');
                const placeholder = document.getElementById('qr_placeholder');

                if (!locSelect || !catSelect || !dateInput || !previewEl) return;

                const locId = locSelect.value;
                const catId = catSelect.value;
                const date = dateInput.value;
                const name = nameInput ? nameInput.value : '';

                // Get current uqcode from the item (already exists)
                const currentUqcode = "{{ $item->uqcode }}";
                const originalLocId = "{{ $item->location_id }}";
                const originalCatId = "{{ $item->category_id }}";

                // If item already has uqcode and data is complete, show QR immediately
                if (currentUqcode && name && locId && catId) {
                    if (placeholder) placeholder.style.display = 'none';

                    // If location/category unchanged, use existing uqcode
                    if (locId == originalLocId && catId == originalCatId) {
                        previewEl.value = currentUqcode;
                        const locName = locSelect.options[locSelect.selectedIndex].text;
                        const catName = catSelect.options[catSelect.selectedIndex].text;
                        renderQR(currentUqcode, name, locName, catName);
                        return;
                    }
                }

                // INITIAL CHECK: Show placeholder if data incomplete
                if (!locId || !catId || !date || !name) {
                    previewEl.value = "Melengkapi data...";
                    if (placeholder) placeholder.style.display = 'flex';
                    return;
                }

                if (placeholder) placeholder.style.display = 'none';

                // Location or category changed, calculate new uqcode
                const locOpt = locSelect.options[locSelect.selectedIndex];
                const catOpt = catSelect.options[catSelect.selectedIndex];
                if (!locOpt || !catOpt) return;

                const locCode = locOpt.getAttribute('data-code');
                const catCode = catOpt.getAttribute('data-code');
                const year = new Date(date).getFullYear();

                previewEl.value = "Sedang menghitung...";

                try {
                    const response = await fetch(
                        `{{ route('api.items.next-serial') }}?location_id=${locId}&category_id=${catId}`);
                    const data = await response.json();
                    const uqcode = `${locCode}.${catCode}.${data.serial}.${year}`;
                    previewEl.value = uqcode;
                    renderQR(uqcode, name, locOpt.text, catOpt.text);
                } catch (e) {
                    console.error(e);
                    previewEl.value = "Koneksi Bermasalah";
                }
            }

            function debouncePreview() {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(updatePreview, 500);
            }

            function toggleInterval() {
                const checkbox = document.getElementById('service_required');
                const section = document.getElementById('interval_section');
                const input = document.getElementById('service_interval_days');
                if (!checkbox || !section || !input) return;

                if (checkbox.checked) {
                    section.style.display = 'block';
                    input.setAttribute('required', 'required');
                } else {
                    section.style.display = 'none';
                    input.removeAttribute('required');
                    input.value = '';
                }
            }

            function updateBtnText() {
                const cb = document.getElementById('auto_qr');
                const btn = document.getElementById('submit_btn');
                if (!cb || !btn) return;
                btn.innerText = cb.checked ? "Update dan Unduh QR" : "Update Barang";
            }

            function initPage() {
                // Reset instance and retry count for fresh initialization
                qrcodeInstance = null;
                initRetryCount = 0;

                const fields = ['location_id', 'category_id', 'acquisition_date', 'item_name'];
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (el.tagName === 'INPUT') el.addEventListener('input', debouncePreview);
                        else el.addEventListener('change', updatePreview);
                    }
                });

                const serviceRequiredCb = document.getElementById('service_required');
                if (serviceRequiredCb) serviceRequiredCb.addEventListener('change', toggleInterval);

                const autoQrCb = document.getElementById('auto_qr');
                if (autoQrCb) autoQrCb.addEventListener('change', updateBtnText);

                updateBtnText();

                // Wait for QRCode library before updating preview
                waitForQRCodeLibrary(updatePreview);
            }

            // Support both standard load and Livewire navigate
            document.addEventListener('DOMContentLoaded', initPage);
            document.addEventListener('livewire:navigated', function() {
                // Small delay for Livewire navigation to ensure DOM is ready
                setTimeout(initPage, 50);
            });

            // Immediate init if page is already loaded (for script loaded after DOM ready)
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(initPage, 50);
            }
        })();

        // Photo Preview Functions (global scope)
        function previewPhoto(input) {
            const previewBox = document.getElementById('photo_preview_box');
            const previewImg = document.getElementById('photo_preview_img');
            const placeholder = document.getElementById('photo_placeholder');
            const clearBtn = document.getElementById('photo_clear_btn');
            const revertBtn = document.getElementById('revert_photo_btn');

            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewBox.style.display = 'block';
                    placeholder.style.display = 'none';
                    if (clearBtn) clearBtn.style.display = 'flex';
                    if (revertBtn) revertBtn.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        }

        function clearPhotoPreview() {
            const input = document.getElementById('photo_input');
            input.value = '';
            revertToOriginal();
        }

        function revertToOriginal() {
            const previewBox = document.getElementById('photo_preview_box');
            const previewImg = document.getElementById('photo_preview_img');
            const placeholder = document.getElementById('photo_placeholder');
            const input = document.getElementById('photo_input');
            const clearBtn = document.getElementById('photo_clear_btn');
            const revertBtn = document.getElementById('revert_photo_btn');

            const originalSrc = previewImg.getAttribute('data-original');

            input.value = '';

            if (originalSrc) {
                previewImg.src = originalSrc;
                previewBox.style.display = 'block';
                placeholder.style.display = 'none';
                if (clearBtn) clearBtn.style.display = 'none';
            } else {
                previewImg.src = '';
                previewBox.style.display = 'none';
                placeholder.style.display = 'flex';
            }

            if (revertBtn) revertBtn.style.display = 'none';
        }
    </script>
@endsection
