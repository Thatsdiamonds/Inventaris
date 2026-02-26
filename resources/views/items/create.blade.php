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
        {{-- Hidden fields for group --}}
        <input type="hidden" name="group_id" id="selected_group_id"
            value="{{ old('group_id', $preselectedGroup ? $preselectedGroup->id : '') }}">
        <input type="hidden" name="name" id="selected_group_name"
            value="{{ old('name', $preselectedGroup ? $preselectedGroup->name : '') }}">

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: var(--spacing-xl); align-items: start;">

            <!-- Left Side: Form Data -->
            <div class="card">
                <h3 class="mb-3"
                    style="font-size: 1.1rem; border-bottom: 1px solid var(--color-border-light); padding-bottom: 0.5rem;">
                    Informasi Dasar</h3>

                {{-- Grouping by Name --}}
                <div class="form-group">
                    <label>Nama Aset (Grup) <span style="color: var(--color-danger);">*</span></label>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div id="selected_group_display"
                            style="flex: 1; padding: 0.6rem 0.85rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg-secondary); min-height: 2.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            @if (old('name', $preselectedGroup ? $preselectedGroup->name : ''))
                                <strong
                                    style="color: var(--color-primary);">{{ old('name', $preselectedGroup ? $preselectedGroup->name : '') }}</strong>
                                @if ($preselectedGroup)
                                    <code
                                        style="font-size:0.78rem;color:var(--color-text-muted);">{{ $preselectedGroup->unique_code }}</code>
                                @endif
                            @else
                                <span class="text-muted" id="group_placeholder_text">Belum dipilih</span>
                            @endif
                        </div>
                        <button type="button" id="open_group_modal_btn" onclick="openGroupModal()"
                            class="btn btn-secondary" style="white-space: nowrap; flex-shrink: 0;">
                            <svg class="icon icon-sm">
                                <use href="#icon-search"></use>
                            </svg>
                            Pilih atau Kelola Nama Aset
                        </button>
                    </div>
                    <small class="text-muted" style="display: block; margin-top: 0.25rem;">Pilih dari katalog atau buat grup
                        nama baru.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label>Tanggal Akuisisi <span style="color: var(--color-danger);">*</span></label>
                        <input type="date" name="acquisition_date" id="acquisition_date"
                            value="{{ old('acquisition_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah <span style="color: var(--color-danger);">*</span></label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1"
                            max="100" placeholder="1">
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

                {{-- Photo --}}
                <div class="form-group">
                    <label>Foto Barang</label>
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        {{-- Preview Box --}}
                        <div id="photo_preview_box"
                            style="width: 100px; height: 100px; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0; display: none; position: relative; border: 2px solid var(--color-border);">
                            <a id="photo_preview_link" href="#" target="_blank" title="Lihat gambar penuh">
                                <img id="photo_preview_img" src=""
                                    style="width: 100%; height: 100%; object-fit: contain; background: #fff; display: block; max-width: 100%; max-height: 100%;">
                            </a>
                            <button type="button" id="photo_clear_btn" onclick="clearPhotoPreview()"
                                style="position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; background: var(--color-danger); color: white; border: 2px solid white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; line-height: 1; padding: 0;">
                                &times;
                            </button>
                        </div>
                        {{-- Placeholder --}}
                        <div id="photo_placeholder"
                            style="width: 100px; height: 100px; background: var(--color-bg-secondary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); flex-shrink: 0; border: 2px dashed var(--color-border); cursor: pointer;"
                            onclick="document.getElementById('photo_input').click()">
                            <svg class="icon icon-lg">
                                <use href="#icon-image"></use>
                            </svg>
                        </div>
                        {{-- File Input --}}
                        <div style="flex: 1;">
                            <input type="file" name="photo" id="photo_input" accept="image/*"
                                style="border: 1px dashed var(--color-border); padding: 0.75rem; width: 100%;"
                                onchange="previewPhoto(this)">
                            <small class="text-muted" style="display: block; margin-top: 0.25rem;">Format: JPG, PNG, WebP.
                                Maks: 2MB. Klik thumbnail untuk lihat penuh.</small>
                        </div>
                    </div>
                </div>

                {{-- UQ Code Preview --}}
                <div
                    style="background: var(--color-bg-secondary); padding: var(--spacing-md); border-radius: var(--radius-md); border-left: 4px solid var(--color-accent);">
                    <label class="mb-1" style="font-weight: 600; color: var(--color-primary);">Kode Barang
                        (Otomatis)</label>
                    <input type="text" id="uqcode_preview" readonly
                        style="border: none; background: transparent; padding: 0; font-family: monospace; font-size: 1.1rem; font-weight: 600; color: var(--color-accent);"
                        value="Melengkapi data...">
                    <p class="text-muted mb-0" style="font-size: 0.75rem; margin-top: 0.25rem;">
                        *Lokasi.Kategori.NamaKode.Serial.Tahun</p>
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

                    <div
                        style="display: flex; flex-direction: column; gap: 0.5rem; background: var(--color-bg-tertiary); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--color-border-light);">
                        <label
                            style="display: flex; align-items: flex-start; gap: 0.5rem; text-align: left; cursor: pointer;">
                            <input type="checkbox" name="auto_qr" id="auto_qr" value="1"
                                {{ $autoDownload ? 'checked' : '' }} style="margin-top: 2px;">
                            <span style="font-size: 0.8rem; font-weight: 500;">Unduh QR otomatis setelah menyimpan
                                data</span>
                        </label>
                        <div style="height: 1px; background: var(--color-border-light); margin: 0.25rem 0;"></div>
                        <label
                            style="display: flex; align-items: flex-start; gap: 0.5rem; text-align: left; cursor: pointer;">
                            <input type="checkbox" name="auto_print" id="auto_print" value="1"
                                style="margin-top: 2px;">
                            <span style="font-size: 0.8rem; font-weight: 500; color: var(--color-accent);">Cetak Label
                                otomatis (Quick Print)</span>
                        </label>
                    </div>
                </div>

                <button type="submit" id="submit_btn" class="btn btn-primary btn-lg"
                    style="width: 100%; font-weight: 600;">Simpan Barang</button>
                <a href="{{ route('items.index') }}" wire:navigate class="btn btn-ghost"
                    style="width: 100%; text-align: center; justify-content: center;">Batal</a>
            </div>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════════
         ASSET NAME CATALOG MODAL (2-column, no animation)
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="groupModal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
        <div
            style="background: white; border-radius: var(--radius-lg); box-shadow: 0 8px 40px rgba(0,0,0,0.18); width: 100%; max-width: 860px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">

            {{-- Modal Header --}}
            <div
                style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light);">
                <h3 style="margin: 0; font-size: 1.05rem;">Katalog Nama Aset</h3>
                <button type="button" onclick="closeGroupModal()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted); line-height: 1; padding: 0;">&times;</button>
            </div>

            {{-- Modal Body: 2 columns --}}
            <div style="display: grid; grid-template-columns: 320px 1fr; flex: 1; overflow: hidden; min-height: 0;">

                {{-- LEFT: Create / Edit Group --}}
                <div
                    style="border-right: 1px solid var(--color-border-light); padding: 1.25rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem;">
                    <h4
                        style="margin: 0 0 0.5rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted);">
                        <span id="modal_form_title">Buat Grup Baru</span>
                    </h4>

                    <div id="modal_alert"
                        style="display: none; padding: 0.6rem 0.75rem; border-radius: var(--radius-md); font-size: 0.82rem; margin-bottom: 0.25rem;">
                    </div>

                    <input type="hidden" id="modal_edit_id" value="">

                    <div>
                        <label style="font-size: 0.82rem; font-weight: 600; display: block; margin-bottom: 0.25rem;">Nama
                            Aset <span style="color: var(--color-danger);">*</span></label>
                        <input type="text" id="modal_name" placeholder="Contoh: Laptop Dell XPS 15"
                            style="width: 100%; box-sizing: border-box;" oninput="onModalNameInput()">
                    </div>

                    <div>
                        <label style="font-size: 0.82rem; font-weight: 600; display: block; margin-bottom: 0.25rem;">Kode
                            Prefix (Unik) <span style="color: var(--color-danger);">*</span></label>
                        <input type="text" id="modal_code" placeholder="Auto-generate"
                            style="width: 100%; box-sizing: border-box; font-family: monospace; text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'')">
                        <small
                            style="color: var(--color-text-muted); font-size: 0.75rem; display: block; margin-top: 0.2rem;">Maks
                            10 karakter. Huruf & angka saja.</small>
                    </div>

                    <div id="modal_existing_info"
                        style="display: none; background: var(--color-bg-secondary); border-radius: var(--radius-md); padding: 0.6rem 0.75rem; font-size: 0.8rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.2rem;">
                            <span class="text-muted">Total Barang:</span>
                            <strong id="modal_info_count">0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span class="text-muted">Kode Terakhir:</span>
                            <code id="modal_info_latest" style="font-size: 0.75rem;">-</code>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem;">
                        <button type="button" id="modal_save_btn" onclick="saveGroup()" class="btn btn-primary btn-sm"
                            style="flex: 1;">
                            Simpan Grup
                        </button>
                        <button type="button" id="modal_cancel_edit_btn" onclick="cancelModalEdit()"
                            class="btn btn-ghost btn-sm" style="display: none;">
                            Batal
                        </button>
                    </div>
                </div>

                {{-- RIGHT: Search & Select --}}
                <div style="display: flex; flex-direction: column; overflow: hidden;">
                    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light);">
                        <h4
                            style="margin: 0 0 0.6rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted);">
                            Pilih dari Katalog</h4>
                        <div style="position: relative;">
                            <input type="text" id="modal_search" placeholder="Cari nama aset..."
                                style="width: 100%; box-sizing: border-box; padding-left: 2.25rem;"
                                oninput="debounceGroupSearch()">
                            <svg class="icon icon-sm"
                                style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">
                                <use href="#icon-search"></use>
                            </svg>
                        </div>
                    </div>
                    <div id="modal_group_list" style="flex: 1; overflow-y: auto; padding: 0.5rem 0;">
                        <div
                            style="text-align: center; padding: 2rem; color: var(--color-text-muted); font-size: 0.85rem;">
                            Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function() {
            let qrcodeInstance = null;
            let previewTimeout = null;

            // ─── QR / UQ Code Preview ────────────────────────────────────────────

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

            async function updatePreview() {
                const locSelect = document.getElementById('location_id');
                const catSelect = document.getElementById('category_id');
                const dateInput = document.getElementById('acquisition_date');
                const previewEl = document.getElementById('uqcode_preview');
                const groupId = document.getElementById('selected_group_id').value;
                const groupName = document.getElementById('selected_group_name').value;
                const placeholder = document.getElementById('qr_placeholder');

                if (!locSelect || !catSelect || !dateInput || !previewEl) return;

                const locId = locSelect.value;
                const catId = catSelect.value;
                const date = dateInput.value;

                if (!locId || !catId || !date || !groupName) {
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
                    const params = new URLSearchParams({
                        location_id: locId,
                        category_id: catId,
                        name: groupName,
                    });
                    if (groupId) params.append('group_id', groupId);

                    const response = await fetch(`{{ route('api.items.next-serial') }}?${params}`);
                    const data = await response.json();
                    const uqcode = `${locCode}.${catCode}.${data.name_code}.${data.serial}.${year}`;
                    previewEl.value = uqcode;

                    if (!qrcodeInstance) qrcodeInstance = initQRCode();
                    if (qrcodeInstance) {
                        const qrText =
                            `Kode: ${uqcode}\nNama: ${groupName}\nLokasi: ${locName}\nKategori: ${catName}`;
                        qrcodeInstance.clear();
                        qrcodeInstance.makeCode(qrText);
                    }
                } catch (e) {
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
                const ap = document.getElementById('auto_print');
                const btn = document.getElementById('submit_btn');
                if (!cb || !btn) return;

                if (ap && ap.checked) {
                    btn.innerText = "Simpan dan Cetak Label";
                    btn.style.backgroundColor = "var(--color-accent)";
                } else if (cb.checked) {
                    btn.innerText = "Simpan dan Unduh QR";
                    btn.style.backgroundColor = "";
                } else {
                    btn.innerText = "Simpan Barang";
                    btn.style.backgroundColor = "";
                }
            }

            function initPage() {
                ['location_id', 'category_id', 'acquisition_date'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', debouncePreview);
                });
                const serviceRequiredCb = document.getElementById('service_required');
                if (serviceRequiredCb) serviceRequiredCb.addEventListener('change', toggleInterval);

                const autoQrCb = document.getElementById('auto_qr');
                if (autoQrCb) autoQrCb.addEventListener('change', () => {
                    if (autoQrCb.checked && document.getElementById('auto_print')) {
                        document.getElementById('auto_print').checked = false;
                    }
                    updateBtnText();
                });

                const autoPrintCb = document.getElementById('auto_print');
                if (autoPrintCb) autoPrintCb.addEventListener('change', () => {
                    if (autoPrintCb.checked && document.getElementById('auto_qr')) {
                        document.getElementById('auto_qr').checked = false;
                    }
                    updateBtnText();
                });

                updateBtnText();
                updatePreview();
            }

            document.addEventListener('DOMContentLoaded', initPage);
            document.addEventListener('livewire:navigated', initPage);
            if (document.readyState === 'complete' || document.readyState === 'interactive') initPage();

            // ─── Photo Preview ───────────────────────────────────────────────────

            window.previewPhoto = function(input) {
                const previewBox = document.getElementById('photo_preview_box');
                const previewImg = document.getElementById('photo_preview_img');
                const previewLink = document.getElementById('photo_preview_link');
                const placeholder = document.getElementById('photo_placeholder');

                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar. Maksimal 2MB.');
                        input.value = '';
                        return;
                    }
                    if (!file.type.match('image.*')) {
                        alert('File harus berupa gambar.');
                        input.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        if (previewLink) previewLink.href = e.target.result;
                        previewBox.style.display = 'block';
                        placeholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            };

            window.clearPhotoPreview = function() {
                document.getElementById('photo_preview_img').src = '';
                document.getElementById('photo_preview_box').style.display = 'none';
                document.getElementById('photo_placeholder').style.display = 'flex';
                document.getElementById('photo_input').value = '';
            };

            // ─── Group Modal ─────────────────────────────────────────────────────

            let groupSearchTimer = null;

            window.openGroupModal = function() {
                document.getElementById('groupModal').style.display = 'flex';
                loadGroups();
            };

            window.closeGroupModal = function() {
                document.getElementById('groupModal').style.display = 'none';
            };

            window.debounceGroupSearch = function() {
                clearTimeout(groupSearchTimer);
                groupSearchTimer = setTimeout(loadGroups, 300);
            };

            window.onModalNameInput = function() {
                const name = document.getElementById('modal_name').value.trim();
                const codeInput = document.getElementById('modal_code');
                if (!document.getElementById('modal_edit_id').value) {
                    // Auto-generate code from name
                    const code = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
                    codeInput.value = code;
                }
            };

            window.loadGroups = async function() {
                const q = document.getElementById('modal_search').value;
                const list = document.getElementById('modal_group_list');
                list.innerHTML =
                    '<div style="text-align:center;padding:1.5rem;color:var(--color-text-muted);font-size:0.85rem;">Memuat...</div>';
                try {
                    const res = await fetch(`{{ route('api.item-types.list') }}?q=${encodeURIComponent(q)}`);
                    const groups = await res.json();
                    renderGroupList(groups);
                } catch (e) {
                    list.innerHTML =
                        '<div style="text-align:center;padding:1.5rem;color:var(--color-danger);font-size:0.85rem;">Gagal memuat data.</div>';
                }
            };

            function renderGroupList(groups) {
                const list = document.getElementById('modal_group_list');
                if (!groups.length) {
                    list.innerHTML =
                        '<div style="text-align:center;padding:2rem;color:var(--color-text-muted);font-size:0.85rem;">Belum ada grup. Buat di sisi kiri.</div>';
                    return;
                }
                list.innerHTML = '';
                groups.forEach(g => {
                    const row = document.createElement('div');
                    row.style.cssText =
                        'display:flex;align-items:center;justify-content:space-between;padding:0.65rem 1.25rem;border-bottom:1px solid var(--color-border-light);cursor:pointer;';
                    row.onmouseenter = () => row.style.background = 'var(--color-bg-secondary)';
                    row.onmouseleave = () => row.style.background = '';
                    row.innerHTML = `
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:0.9rem;color:var(--color-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(g.name)}</div>
                        <div style="font-size:0.75rem;color:var(--color-text-muted);margin-top:0.15rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
                            <span>Kode: <code style="font-size:0.72rem;">${escHtml(g.unique_code)}</code></span>
                            <span>Barang: <strong style="color:var(--color-accent);">${g.item_count}</strong></span>
                            ${g.latest_code ? `<span>Terakhir: <code style="font-size:0.72rem;">${escHtml(g.latest_code)}</code></span>` : ''}
                        </div>
                    </div>
                    <div style="display:flex;gap:0.4rem;flex-shrink:0;margin-left:0.75rem;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="editGroup(event,${g.id},'${escAttr(g.name)}','${escAttr(g.unique_code)}',${g.item_count},'${escAttr(g.latest_code||'')}')" title="Edit">
                            <svg class="icon icon-sm"><use href="#icon-edit"></use></svg>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="selectGroup(${g.id},'${escAttr(g.name)}','${escAttr(g.unique_code)}')">Pilih</button>
                    </div>
                `;
                    list.appendChild(row);
                });
            }

            window.selectGroup = function(id, name, code) {
                document.getElementById('selected_group_id').value = id;
                document.getElementById('selected_group_name').value = name;

                const display = document.getElementById('selected_group_display');
                display.innerHTML =
                    `<strong style="color:var(--color-primary);">${escHtml(name)}</strong><code style="font-size:0.78rem;color:var(--color-text-muted);margin-left:0.4rem;">${escHtml(code)}</code>`;

                closeGroupModal();
                debouncePreview();
            };

            window.editGroup = function(e, id, name, code, count, latest) {
                e.stopPropagation();
                document.getElementById('modal_edit_id').value = id;
                document.getElementById('modal_name').value = name;
                document.getElementById('modal_code').value = code;
                document.getElementById('modal_form_title').textContent = 'Edit Grup';
                document.getElementById('modal_cancel_edit_btn').style.display = '';
                document.getElementById('modal_existing_info').style.display = 'block';
                document.getElementById('modal_info_count').textContent = count;
                document.getElementById('modal_info_latest').textContent = latest || '-';
            };

            window.cancelModalEdit = function() {
                document.getElementById('modal_edit_id').value = '';
                document.getElementById('modal_name').value = '';
                document.getElementById('modal_code').value = '';
                document.getElementById('modal_form_title').textContent = 'Buat Grup Baru';
                document.getElementById('modal_cancel_edit_btn').style.display = 'none';
                document.getElementById('modal_existing_info').style.display = 'none';
                hideModalAlert();
            };

            window.saveGroup = async function() {
                const id = document.getElementById('modal_edit_id').value;
                const name = document.getElementById('modal_name').value.trim();
                const code = document.getElementById('modal_code').value.trim();

                if (!name) {
                    showModalAlert('Nama aset wajib diisi.', 'error');
                    return;
                }
                if (!code) {
                    showModalAlert('Kode prefix wajib diisi.', 'error');
                    return;
                }

                const btn = document.getElementById('modal_save_btn');
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';

                try {
                    const body = new FormData();
                    body.append('_token', '{{ csrf_token() }}');
                    if (id) body.append('id', id);
                    body.append('name', name);
                    body.append('unique_code', code);
                    body.append('redirect_url', window.location.href);

                    const res = await fetch('{{ route('api.item-types.save') }}', {
                        method: 'POST',
                        body
                    });
                    const data = await res.json();

                    if (data.success) {
                        if (data.requires_sync) {
                            window.location.href = data.sync_url;
                            return;
                        }
                        showModalAlert('Grup berhasil disimpan.', 'success');
                        cancelModalEdit();
                        loadGroups();
                    } else {
                        const errs = data.errors ? Object.values(data.errors).flat().join(' ') :
                            'Gagal menyimpan.';
                        showModalAlert(errs, 'error');
                    }
                } catch (e) {
                    showModalAlert('Koneksi bermasalah.', 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Simpan Grup';
                }
            };

            function showModalAlert(msg, type) {
                const el = document.getElementById('modal_alert');
                el.textContent = msg;
                el.style.display = 'block';
                el.style.background = type === 'success' ? 'var(--color-success-subtle, #d1fae5)' :
                    'var(--color-danger-subtle, #fee2e2)';
                el.style.color = type === 'success' ? 'var(--color-success, #065f46)' : 'var(--color-danger, #991b1b)';
                el.style.border =
                    `1px solid ${type === 'success' ? 'var(--color-success, #34d399)' : 'var(--color-danger, #f87171)'}`;
            }

            function hideModalAlert() {
                document.getElementById('modal_alert').style.display = 'none';
            }

            function escHtml(str) {
                if (!str) return '';
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;');
            }

            function escAttr(str) {
                if (!str) return '';
                return String(str).replace(/'/g, "\\'");
            }

            // Close modal on backdrop click
            document.getElementById('groupModal').addEventListener('click', function(e) {
                if (e.target === this) closeGroupModal();
            });

            // Pre-fill if old value exists
            @if (old('name'))
                document.getElementById('selected_group_display').innerHTML =
                    '<strong style="color:var(--color-primary);">{{ old('name') }}</strong>';
            @endif

        })();
    </script>
@endsection
