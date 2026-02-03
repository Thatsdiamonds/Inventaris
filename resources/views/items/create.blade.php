@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <h1 class="mb-0">Tambah Barang</h1>
        <p class="text-secondary">Input data barang baru ke sistem</p>
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

    <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: var(--spacing-xl); align-items: start;">

            <!-- Left Side: Form Data -->
            <div class="card">
                <h3 class="mb-3"
                    style="font-size: 1.1rem; border-bottom: 1px solid var(--color-border-light); padding-bottom: 0.5rem;">
                    Informasi Dasar</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label>Nama Barang <span style="color: var(--color-danger);">*</span></label>
                        <input type="text" name="name" id="item_name" value="{{ old('name') }}"
                            placeholder="Contoh: AC Panasonic 2PK" required>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Akuisisi <span style="color: var(--color-danger);">*</span></label>
                        <input type="date" name="acquisition_date" id="acquisition_date"
                            value="{{ old('acquisition_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label>Kategori <span style="color: var(--color-danger);">*</span></label>
                        <select name="category_id" id="category_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-code="{{ $category->unique_code }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Lokasi <span style="color: var(--color-danger);">*</span></label>
                        <select name="location_id" id="location_id" required>
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" data-code="{{ $location->unique_code }}"
                                    {{ old('location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kondisi <span style="color: var(--color-danger);">*</span></label>
                    <select name="condition" required>
                        <option value="baik" {{ old('condition') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak" {{ old('condition') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                        <option value="perbaikan" {{ old('condition') == 'perbaikan' ? 'selected' : '' }}>Perbaikan
                        </option>
                        <option value="dimusnahkan" {{ old('condition') == 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Foto Barang</label>
                    <div id="photo_preview_container" style="display: flex; align-items: flex-start; gap: 1rem;">
                        <!-- Preview Box -->
                        <div id="photo_preview_box"
                            style="width: 80px; height: 80px; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0; display: none; position: relative; border: 2px solid var(--color-border);">
                            <img id="photo_preview_img" src=""
                                style="width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" id="photo_clear_btn" onclick="clearPhotoPreview()"
                                style="position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; background: var(--color-danger); color: white; border: 2px solid white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; line-height: 1; padding: 0;">
                                ×
                            </button>
                        </div>
                        <!-- Placeholder when no image -->
                        <div id="photo_placeholder"
                            style="width: 80px; height: 80px; background: var(--color-bg-secondary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); flex-shrink: 0; border: 2px dashed var(--color-border);">
                            <svg class="icon icon-lg">
                                <use href="#icon-image"></use>
                            </svg>
                        </div>
                        <!-- File Input -->
                        <div style="flex: 1;">
                            <input type="file" name="photo" id="photo_input" accept="image/*"
                                style="border: 1px dashed var(--color-border); padding: 0.75rem; width: 100%;"
                                onchange="previewPhoto(this)">
                            <small class="text-muted" style="display: block; margin-top: 0.25rem;">Format: JPG, PNG, WebP.
                                Maks: 2MB.</small>
                        </div>
                    </div>
                </div>

                <div
                    style="background: var(--color-bg-secondary); padding: var(--spacing-md); border-radius: var(--radius-md); border-left: 4px solid var(--color-accent);">
                    <label class="mb-1" style="font-weight: 600; color: var(--color-primary);">Kode Barang
                        (Otomatis)</label>
                    <input type="text" id="uqcode_preview" readonly
                        style="border: none; background: transparent; padding: 0; font-family: monospace; font-size: 1.1rem; font-weight: 600; color: var(--color-accent);"
                        value="Melengkapi data...">
                    <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">
                        *Lokasi.Kategori.Serial.Tahun</p>
                </div>

                <div class="mt-4"
                    style="border-top: 1px solid var(--color-border-light); padding-top: var(--spacing-lg);">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="service_required" id="service_required" value="1"
                            {{ old('service_required') ? 'checked' : '' }} style="width: 1.2rem; height: 1.2rem;">
                        <div>
                            <strong style="display: block; font-size: 0.9375rem;">Aktifkan Jadwal Perawatan</strong>
                            <span class="text-muted" style="font-size: 0.8rem;">Pengingat otomatis untuk servis barang
                                secara rutin.</span>
                        </div>
                    </label>

                    <div id="interval_section"
                        style="{{ old('service_required') ? 'display: block;' : 'display: none;' }} margin-top: var(--spacing-md); padding-left: 2rem;">
                        <label style="font-size: 0.875rem;">Interval Servis (Hari)</label>
                        <input type="number" name="service_interval_days" id="service_interval_days"
                            placeholder="Contoh: 90" style="max-width: 150px;"
                            value="{{ old('service_interval_days') }}">
                    </div>
                </div>
            </div>

            <!-- Right Side: QR Preview & Submit -->
            <div style="position: sticky; top: 2rem; display: flex; flex-direction: column; gap: var(--spacing-lg);">
                <div class="card" style="text-align: center;">
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
                    <p class="text-muted" style="font-size: 0.75rem; line-height: 1.4;">QR Code ini digunakan untuk label
                        barang dan akses cepat riwayat servis.</p>

                    @php
                        $setting = \App\Models\Setting::first();
                        $autoDownload = $setting ? $setting->auto_download_after_add : true;
                    @endphp

                    <label
                        style="display: flex; align-items: flex-start; gap: 0.5rem; text-align: left; background: var(--color-bg-tertiary); padding: 0.75rem; border-radius: var(--radius-md); cursor: pointer; border: 1px solid var(--color-border-light);">
                        <input type="checkbox" name="auto_qr" id="auto_qr" value="1"
                            {{ $autoDownload ? 'checked' : '' }} style="margin-top: 2px;">
                        <span style="font-size: 0.8rem; font-weight: 500;">Unduh QR otomatis setelah menyimpan data</span>
                    </label>
                </div>

                <button type="submit" id="submit_btn" class="btn btn-primary btn-lg"
                    style="width: 100%; font-weight: 600;">Simpan Barang</button>
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

            function initQRCode() {
                const container = document.getElementById("qrcode");
                if (!container) return;

                container.innerHTML = ""; // Clear existing

                if (typeof QRCode === 'undefined') {
                    container.innerHTML = "<small class='text-danger'>QR library not loaded</small>";
                    return null;
                }

                return new QRCode(container, {
                    width: 180,
                    height: 180,
                    colorDark: "#2c3e50",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
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

                // INITIAL CHECK: Sinkronisasi visual jika data belum lengkap
                if (!locId || !catId || !date || !name) {
                    previewEl.value = "Melengkapi data...";
                    if (placeholder) placeholder.style.display = 'flex';
                    return;
                }

                if (placeholder) placeholder.style.display = 'none';

                const locOpt = locSelect.options[locSelect.selectedIndex];
                const catOpt = catSelect.options[catSelect.selectedIndex];
                const locCode = locOpt.getAttribute('data-code');
                const catCode = catOpt.getAttribute('data-code');
                const locName = locOpt.text;
                const catName = catOpt.text;
                const year = new Date(date).getFullYear();

                previewEl.value = "Sedang menghitung...";

                try {
                    const response = await fetch(
                        `{{ route('api.items.next-serial') }}?location_id=${locId}&category_id=${catId}`);
                    const data = await response.json();
                    const uqcode = `${locCode}.${catCode}.${data.serial}.${year}`;
                    previewEl.value = uqcode;

                    if (!qrcodeInstance) {
                        qrcodeInstance = initQRCode();
                    }

                    if (qrcodeInstance) {
                        const qrText =
                            `Kode: ${uqcode}\nNama: ${name || '-'}\nLokasi: ${locName}\nKategori: ${catName}`;
                        qrcodeInstance.clear();
                        qrcodeInstance.makeCode(qrText);
                    }
                } catch (e) {
                    console.error("Preview update error:", e);
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
                btn.innerText = cb.checked ? "Simpan dan Unduh QR" : "Simpan Barang";
            }

            function initPage() {
                // Attach events
                const inputs = ['location_id', 'category_id', 'acquisition_date', 'item_name'];
                inputs.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (el.tagName === 'INPUT') {
                            el.addEventListener('input', debouncePreview);
                        } else {
                            el.addEventListener('change', updatePreview);
                        }
                    }
                });

                const serviceRequiredCb = document.getElementById('service_required');
                if (serviceRequiredCb) serviceRequiredCb.addEventListener('change', toggleInterval);

                const autoQrCb = document.getElementById('auto_qr');
                if (autoQrCb) autoQrCb.addEventListener('change', updateBtnText);

                // Initial run
                updateBtnText();
                updatePreview();
            }

            // Support both standard load and Livewire navigate
            document.addEventListener('DOMContentLoaded', initPage);
            document.addEventListener('livewire:navigated', initPage);

            // In case it's already loaded
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                initPage();
            }
        })();

        // Photo Preview Functions (global scope)
        function previewPhoto(input) {
            const previewBox = document.getElementById('photo_preview_box');
            const previewImg = document.getElementById('photo_preview_img');
            const placeholder = document.getElementById('photo_placeholder');

            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    input.value = '';
                    return;
                }

                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('File harus berupa gambar.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewBox.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        function clearPhotoPreview() {
            const previewBox = document.getElementById('photo_preview_box');
            const previewImg = document.getElementById('photo_preview_img');
            const placeholder = document.getElementById('photo_placeholder');
            const input = document.getElementById('photo_input');

            previewImg.src = '';
            previewBox.style.display = 'none';
            placeholder.style.display = 'flex';
            input.value = '';
        }
    </script>
@endsection
