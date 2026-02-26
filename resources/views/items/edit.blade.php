@extends('layouts.app')

@section('content')
    @php
        $isLocked = $item->condition == 'perbaikan';
    @endphp

    <div class="page-header mb-4 flex-between">
        <div>
            <h1 class="mb-1">Pembaruan Data Aset</h1>
            <p class="text-secondary text-sm">Perbarui detail dan status aset <span
                    class="font-mono bg-gray-100 px-1 rounded">#{{ $item->uqcode }}</span></p>
        </div>
        @if ($isLocked)
            <div
                class="bg-warning-subtle text-warning-dark border border-warning px-3 py-2 rounded-md flex items-center gap-2 text-sm font-bold animate-pulse">
                <svg class="icon icon-sm">
                    <use href="#icon-alert"></use>
                </svg>
                <span>Status: Dalam Perbaikan (Data Kritis Terkunci)</span>
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert alert-error slide-in-down mb-4">
            <ul class="mb-0 pl-4 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        {{-- Hidden fields for group --}}
        <input type="hidden" name="group_id" id="selected_group_id" value="{{ old('group_id', $item->group_id) }}">
        <input type="hidden" name="name" id="selected_group_name" value="{{ old('name', $item->name) }}">

        <div class="grid lg:grid-cols-[1fr_340px] gap-6 items-start"
            style="display: grid; grid-template-columns: 1fr 340px; gap: 24px;">

            <!-- Left Side: Form Data -->
            <div class="card p-6">
                @if ($isLocked)
                    <div class="bg-blue-50 text-blue-800 p-3 rounded mb-4 text-xs flex gap-2 border-l-4 border-blue-400">
                        <svg class="icon icon-sm shrink-0">
                            <use href="#icon-info"></use>
                        </svg>
                        <p>Kategori dan Lokasi tidak dapat diubah selama aset dalam status perbaikan untuk menjaga
                            integritas riwayat servis.</p>
                    </div>
                @endif

                <h3 class="text-lg font-bold border-b border-light pb-3 mb-4 text-dark">Detail Utama</h3>

                <div class="grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Nama Aset (Grup) <span class="text-danger">*</span></label>
                        @if (!$isLocked)
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div id="selected_group_display"
                                    style="flex: 1; padding: 0.6rem 0.85rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg-secondary); min-height: 2.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <strong style="color: var(--color-primary);">{{ old('name', $item->name) }}</strong>
                                    @if ($item->group)
                                        <code
                                            style="font-size:0.78rem;color:var(--color-text-muted);">{{ $item->group->unique_code }}</code>
                                    @endif
                                </div>
                                <button type="button" onclick="openGroupModal()" class="btn btn-secondary"
                                    style="white-space: nowrap; flex-shrink: 0;">
                                    <svg class="icon icon-sm">
                                        <use href="#icon-search"></use>
                                    </svg>
                                    Pilih atau Kelola Nama Aset
                                </button>
                            </div>
                        @else
                            <input type="text" value="{{ $item->name }}" class="form-control" readonly>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Perolehan <span class="text-danger">*</span></label>
                        <input type="date" name="acquisition_date" id="acquisition_date"
                            value="{{ old('acquisition_date', $item->acquisition_date ? $item->acquisition_date->format('Y-m-d') : '') }}"
                            class="form-control" required {{ $isLocked ? 'disabled' : '' }}>
                        @if ($isLocked)
                            <input type="hidden" name="acquisition_date"
                                value="{{ $item->acquisition_date->format('Y-m-d') }}">
                        @endif
                    </div>
                </div>

                <div class="grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control" required
                            {{ $isLocked ? 'disabled' : '' }}>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-code="{{ $category->unique_code }}"
                                    {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isLocked)
                            <input type="hidden" name="category_id" value="{{ $item->category_id }}">
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi Penempatan <span class="text-danger">*</span></label>
                        <select name="location_id" id="location_id" class="form-control" required
                            {{ $isLocked ? 'disabled' : '' }}>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" data-code="{{ $location->unique_code }}"
                                    {{ old('location_id', $item->location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isLocked)
                            <input type="hidden" name="location_id" value="{{ $item->location_id }}">
                        @endif
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Kondisi Aset <span class="text-danger">*</span></label>
                    <select name="condition" id="item_condition" class="form-control" required
                        {{ $isLocked ? 'disabled' : '' }}>
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

                <div class="form-group mb-4">
                    <label class="form-label">Update Foto Aset</label>
                    <div class="flex gap-4 items-start">
                        <!-- Preview Box -->
                        <div id="photo_preview_box"
                            class="relative w-32 h-32 rounded overflow-hidden border border-light bg-white shadow-sm {{ $item->photo_path ? '' : 'hidden' }}"
                            style="width: 100px; height: 100px;">
                            <a id="photo_preview_link"
                                href="{{ $item->photo_path ? asset('storage/' . $item->photo_path) : '#' }}"
                                target="_blank" title="Lihat gambar penuh">
                                <img id="photo_preview_img"
                                    src="{{ $item->photo_path ? asset('storage/' . $item->photo_path) : '' }}"
                                    data-original="{{ $item->photo_path ? asset('storage/' . $item->photo_path) : '' }}"
                                    style="width: 100%; height: 100%; object-fit: contain; background: #fff; display: block; max-width: 100%; max-height: 100%;">
                            </a>
                            <button type="button" id="revert_photo_btn" onclick="revertToOriginal()" class="hidden"
                                title="Batalkan Perubahan"
                                style="position:absolute;top:-6px;right:-6px;width:22px;height:22px;border-radius:50%;background:var(--color-danger);color:white;border:2px solid white;cursor:pointer;display:none;align-items:center;justify-content:center;font-size:14px;line-height:1;padding:0;">
                                &times;
                            </button>
                        </div>

                        <!-- Placeholder -->
                        <div id="photo_placeholder"
                            class="w-32 h-32 rounded bg-gray-50 flex-center border-2 border-dashed border-gray-300 text-muted hover:bg-gray-100 transition-colors cursor-pointer {{ $item->photo_path ? 'hidden' : '' }}"
                            onclick="document.getElementById('photo_input').click()">
                            <div class="text-center p-2">
                                <svg class="icon icon-lg mb-1 mx-auto">
                                    <use href="#icon-image"></use>
                                </svg>
                                <span class="text-[10px] uppercase font-bold tracking-wide">Ganti Foto</span>
                            </div>
                        </div>

                        <div class="flex-1 pt-1">
                            <input type="file" name="photo" id="photo_input" accept="image/*" class="hidden"
                                onchange="previewPhoto(this)">
                            <button type="button" onclick="document.getElementById('photo_input').click()"
                                class="btn btn-sm btn-outline-secondary mb-1">
                                Pilih File...
                            </button>
                            <small class="text-muted block text-xs">Format: JPG, PNG, WebP. Maks 2MB.</small>
                            <small class="text-muted block text-xs mt-1">Foto lama akan diganti dengan foto baru.</small>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded border-l-4 border-accent mb-4"
                    style="background: var(--c-accent-subtle); border-left-color: var(--c-accent);">
                    <label class="text-primary font-bold text-sm mb-1 block uppercase tracking-wider">Kode Aset Saat
                        Ini</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="uqcode_preview" name="uqcode" readonly
                            class="bg-transparent border-0 p-0 font-mono text-xl font-bold block w-full text-accent focus:ring-0"
                            value="{{ $item->uqcode }}">
                        <span class="badge badge-success px-2 py-1 text-xs">TERDAFTAR</span>
                    </div>
                    <p class="text-muted text-xs mt-1">*Kode akan diperbarui otomatis jika Kategori atau Lokasi berubah.
                    </p>
                </div>

                <hr class="border-light my-4">

                <div class="form-group">
                    <label class="flex gap-3 items-center cursor-pointer p-2 rounded hover:bg-gray-50 transition-colors">
                        <input type="checkbox" name="service_required" id="service_required" value="1"
                            {{ old('service_required', $item->service_required) ? 'checked' : '' }}
                            class="w-5 h-5 text-accent rounded focus:ring-accent">
                        <div>
                            <span class="font-bold text-dark block text-sm">Aktifkan Jadwal Perawatan Rutin</span>
                            <span class="text-xs text-muted block">Sistem akan mengingatkan jadwal servis berkala.</span>
                        </div>
                    </label>

                    <div id="interval_section"
                        class="mt-3 ml-8 {{ old('service_required', $item->service_required) ? 'block' : 'hidden' }}">
                        <label class="form-label text-sm">Interval Servis (Hari)</label>
                        <input type="number" name="service_interval_days" id="service_interval_days"
                            placeholder="Contoh: 90" class="form-control w-32"
                            value="{{ old('service_interval_days', $item->service_interval_days) }}">
                    </div>
                </div>
            </div>

            <!-- Right Side: Action Area -->
            <div class="sticky top-4 flex flex-col gap-4">
                <div class="card p-4 text-center">
                    <div class="relative inline-block w-48 h-48 bg-white border border-light rounded p-2 mb-3">
                        <div id="qrcode" class="w-full h-full flex-center"></div>
                    </div>

                    <p class="text-xs text-muted mb-4 px-2">QR Code akan diperbarui otomatis jika data berubah.</p>

                    @php
                        $setting = \App\Models\Setting::first();
                        $autoDownload = $setting ? $setting->auto_download_after_edit : false;
                    @endphp

                    <label
                        class="flex gap-2 items-start text-left bg-gray-50 p-3 rounded border border-light cursor-pointer hover:bg-gray-100 transition-colors">
                        <input type="checkbox" name="auto_qr" id="auto_qr" value="1"
                            {{ $autoDownload ? 'checked' : '' }} class="mt-1">
                        <span class="text-xs font-medium text-dark">Unduh QR update setelah menyimpan</span>
                    </label>
                </div>

                <button type="submit" id="submit_btn" class="btn btn-primary btn-lg w-full font-bold shadow-lg">Simpan
                    Perubahan</button>
                <a href="{{ route('items.index') }}" wire:navigate
                    class="btn btn-ghost w-full text-center justify-center text-muted">Batal</a>
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

            <div
                style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light);">
                <h3 style="margin: 0; font-size: 1.05rem;">Katalog Nama Aset</h3>
                <button type="button" onclick="closeGroupModal()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted); line-height: 1; padding: 0;">&times;</button>
            </div>

            <div style="display: grid; grid-template-columns: 320px 1fr; flex: 1; overflow: hidden; min-height: 0;">
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
                            style="flex: 1;">Simpan Grup</button>
                        <button type="button" id="modal_cancel_edit_btn" onclick="cancelModalEdit()"
                            class="btn btn-ghost btn-sm" style="display: none;">Batal</button>
                    </div>
                </div>

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

            function waitForQRCodeLibrary(callback, retries = 0) {
                if (typeof QRCode !== 'undefined') callback();
                else if (retries < 10) setTimeout(() => waitForQRCodeLibrary(callback, retries + 1), 100);
            }

            function initQRCode() {
                const container = document.getElementById("qrcode");
                if (!container) return null;
                container.innerHTML = "";
                return new QRCode(container, {
                    width: 170,
                    height: 170,
                    colorDark: "#1e293b",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            async function updatePreview() {
                const locSelect = document.getElementById('location_id');
                const catSelect = document.getElementById('category_id');
                const dateInput = document.getElementById('acquisition_date');
                const previewEl = document.getElementById('uqcode_preview');
                const groupId = document.getElementById('selected_group_id').value;
                const groupName = document.getElementById('selected_group_name').value;

                if (!locSelect || !catSelect || !dateInput || !previewEl) return;

                const locId = locSelect.value;
                const catId = catSelect.value;
                const date = dateInput.value;

                const currentUqcode = "{{ $item->uqcode }}";
                const originalLocId = "{{ $item->location_id }}";
                const originalCatId = "{{ $item->category_id }}";
                const originalName = "{{ $item->name }}";

                // If nothing critical changed, keep current code
                if (locId == originalLocId && catId == originalCatId && groupName == originalName) {
                    previewEl.value = currentUqcode;
                    const locName = locSelect.options[locSelect.selectedIndex].text;
                    if (!qrcodeInstance) qrcodeInstance = initQRCode();
                    if (qrcodeInstance) {
                        qrcodeInstance.clear();
                        qrcodeInstance.makeCode(`Kode: ${currentUqcode}\nNama: ${groupName}\nLokasi: ${locName}`);
                    }
                    return;
                }

                if (!locId || !catId || !date || !groupName) return;

                const locOpt = locSelect.options[locSelect.selectedIndex];
                const catOpt = catSelect.options[catSelect.selectedIndex];
                const locCode = locOpt.getAttribute('data-code');
                const catCode = catOpt.getAttribute('data-code');
                const year = new Date(date).getFullYear();

                previewEl.value = "Menghitung...";

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
                        qrcodeInstance.clear();
                        qrcodeInstance.makeCode(`Kode: ${uqcode}\nNama: ${groupName}\nLokasi: ${locOpt.text}`);
                    }
                } catch (e) {
                    previewEl.value = "Error";
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
                    section.classList.remove('hidden');
                    section.classList.add('block');
                    input.setAttribute('required', 'required');
                } else {
                    section.classList.add('hidden');
                    section.classList.remove('block');
                    input.removeAttribute('required');
                }
            }

            function initPage() {
                qrcodeInstance = null;
                ['location_id', 'category_id', 'acquisition_date'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', debouncePreview);
                });
                const serviceRequiredCb = document.getElementById('service_required');
                if (serviceRequiredCb) serviceRequiredCb.addEventListener('change', toggleInterval);
                waitForQRCodeLibrary(updatePreview);
            }

            document.addEventListener('DOMContentLoaded', initPage);

            // ─── Photo Functions ─────────────────────────────────────────────────

            window.previewPhoto = function(input) {
                const file = input.files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    alert('Maksimal ukuran file 2MB');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('photo_preview_img');
                    const link = document.getElementById('photo_preview_link');
                    const box = document.getElementById('photo_preview_box');
                    const ph = document.getElementById('photo_placeholder');
                    const btn = document.getElementById('revert_photo_btn');
                    img.src = e.target.result;
                    if (link) link.href = e.target.result;
                    box.classList.remove('hidden');
                    ph.classList.add('hidden');
                    if (btn) btn.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            };

            window.revertToOriginal = function() {
                const img = document.getElementById('photo_preview_img');
                const link = document.getElementById('photo_preview_link');
                const originalSrc = img.getAttribute('data-original');
                const input = document.getElementById('photo_input');
                const btn = document.getElementById('revert_photo_btn');

                input.value = '';

                if (originalSrc) {
                    img.src = originalSrc;
                    if (link) link.href = originalSrc;
                    document.getElementById('photo_preview_box').classList.remove('hidden');
                    document.getElementById('photo_placeholder').classList.add('hidden');
                } else {
                    img.src = '';
                    document.getElementById('photo_preview_box').classList.add('hidden');
                    document.getElementById('photo_placeholder').classList.remove('hidden');
                }
                if (btn) btn.style.display = 'none';
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
                if (!document.getElementById('modal_edit_id').value) {
                    const code = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
                    document.getElementById('modal_code').value = code;
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
                if (display) {
                    display.innerHTML =
                        `<strong style="color:var(--color-primary);">${escHtml(name)}</strong><code style="font-size:0.78rem;color:var(--color-text-muted);margin-left:0.4rem;">${escHtml(code)}</code>`;
                }

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

            document.getElementById('groupModal').addEventListener('click', function(e) {
                if (e.target === this) closeGroupModal();
            });

        })();
    </script>
@endsection

let qrcodeInstance = null;
let previewTimeout = null;

// --- Search Modal Logic ---
let searchPage = 1;
let searchLoading = false;
let searchHasMore = true;

window.openSearchModal = function() {
document.getElementById('searchModal').style.display = 'flex';
document.getElementById('searchInput').focus();
loadNames(true);
}

window.closeSearchModal = function() {
document.getElementById('searchModal').style.display = 'none';
}

window.debounceSearch = function() {
if (window.searchDebounceTimer) clearTimeout(window.searchDebounceTimer);
window.searchDebounceTimer = setTimeout(() => loadNames(true), 300);
}

window.loadNames = async function(reset = false) {
if (reset) {
searchPage = 1;
searchHasMore = true;
document.getElementById('searchResults').innerHTML = '';
}
if (!searchHasMore || searchLoading) return;
searchLoading = true;

const query = document.getElementById('searchInput').value;
const container = document.getElementById('searchResults');

if (searchPage === 1)
container.innerHTML =
                    '<div class="text-center p-4 text-muted"><span class="loader-spinner"></span> Loading...</div>';

try {
const res = await fetch(
`{{ route('api.items.search-names') }}?q=${encodeURIComponent(query)}&page=${searchPage}`
);
const data = await res.json();

if (reset) container.innerHTML = '';

if (data.length === 0) {
searchHasMore = false;
if (searchPage === 1)
container.innerHTML =
                            '<div class="text-center p-4 text-muted">Aset tidak ditemukan dalam katalog standar.</div>';
return;
}

data.forEach(item => {
const div = document.createElement('div');
div.className =
                            'p-3 border-b border-light hover:bg-gray-50 cursor-pointer flex items-center justify-between group';
div.onclick = () => selectName(item);
div.innerHTML = `
<div>
    <div class="font-bold text-dark text-sm">${item.name}</div>
    <div class="text-xs text-muted mt-1 flex items-center gap-2">
        <span>Tersedia: <strong class="text-accent">${item.count} unit</strong></span>
        ${item.is_standard ? '<span class="badge badge-success px-1 py-0 text-[10px]">Katalog Baku</span>' : ''}
    </div>
</div>
<button type="button"
    class="btn btn-sm btn-ghost text-xs group-hover:bg-primary group-hover:text-white transition-colors">Pilih</button>
`;
container.appendChild(div);
});

searchPage++;
} catch (e) {
console.error(e);
container.innerHTML = '<div class="text-danger p-4 text-center">Gagal memuat data.</div>';
} finally {
searchLoading = false;
}
}

window.selectName = function(item) {
const el = document.getElementById('item_name');
el.value = item.name;
// Dispatch input event to trigger preview
el.dispatchEvent(new Event('input'));
closeSearchModal();
}

function waitForQRCodeLibrary(callback, retries = 0) {
if (typeof QRCode !== 'undefined') callback();
else if (retries < 10) setTimeout(()=> waitForQRCodeLibrary(callback, retries + 1), 100);
    }

    function initQRCode() {
    const container = document.getElementById("qrcode");
    if (!container) return null;
    container.innerHTML = "";
    return new QRCode(container, {
    width: 170,
    height: 170,
    colorDark: "#1e293b",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.M
    });
    }

    async function updatePreview() {
    const locSelect = document.getElementById('location_id');
    const catSelect = document.getElementById('category_id');
    const dateInput = document.getElementById('acquisition_date');
    const previewEl = document.getElementById('uqcode_preview');
    const nameInput = document.getElementById('item_name');

    if (!locSelect || !catSelect || !dateInput || !previewEl) return;

    const locId = locSelect.value;
    const catId = catSelect.value;
    const date = dateInput.value;
    const name = nameInput ? nameInput.value : '';

    // Original check
    const currentUqcode = "{{ $item->uqcode }}";
    const originalLocId = "{{ $item->location_id }}";
    const originalCatId = "{{ $item->category_id }}";

    // If nothing critical changed, keep current code
    if (locId == originalLocId && catId == originalCatId) {
    previewEl.value = currentUqcode;
    const locName = locSelect.options[locSelect.selectedIndex].text;
    const catName = catSelect.options[catSelect.selectedIndex].text;

    if (!qrcodeInstance) qrcodeInstance = initQRCode();
    if (qrcodeInstance) {
    qrcodeInstance.clear();
    qrcodeInstance.makeCode(`Kode: ${currentUqcode}\nNama: ${name}\nLokasi: ${locName}`);
    }
    return;
    }

    if (!locId || !catId || !date) return;

    const locOpt = locSelect.options[locSelect.selectedIndex];
    const catOpt = catSelect.options[catSelect.selectedIndex];
    const locCode = locOpt.getAttribute('data-code');
    const catCode = catOpt.getAttribute('data-code');
    const year = new Date(date).getFullYear();

    previewEl.value = "Menghitung...";

    try {
    const response = await fetch(
    `{{ route('api.items.next-serial') }}?location_id=${locId}&category_id=${catId}&name=${encodeURIComponent(name)}`
    );
    const data = await response.json();
    const uqcode = `${locCode}.${catCode}.${data.name_code}.${data.serial}.${year}`;

    previewEl.value = uqcode;

    if (!qrcodeInstance) qrcodeInstance = initQRCode();
    if (qrcodeInstance) {
    qrcodeInstance.clear();
    qrcodeInstance.makeCode(`Kode: ${uqcode}\nNama: ${name}\nLokasi: ${locOpt.text}`);
    }
    } catch (e) {
    console.error(e);
    previewEl.value = "Error";
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
    section.classList.remove('hidden');
    section.classList.add('block');
    input.setAttribute('required', 'required');
    } else {
    section.classList.add('hidden');
    section.classList.remove('block');
    input.removeAttribute('required');
    }
    }

    function initPage() {
    qrcodeInstance = null;
    const fields = ['location_id', 'category_id', 'acquisition_date', 'item_name'];
    fields.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', debouncePreview);
    });

    const serviceRequiredCb = document.getElementById('service_required');
    if (serviceRequiredCb) serviceRequiredCb.addEventListener('change', toggleInterval);

    waitForQRCodeLibrary(updatePreview);
    }

    document.addEventListener('DOMContentLoaded', initPage);
    // document.addEventListener('livewire:navigated', () => setTimeout(initPage, 50));
    })();

    // Global Photo Functions
    window.previewPhoto = function(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
    alert('Maksimal ukuran file 2MB');
    input.value = '';
    return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
    document.getElementById('photo_preview_img').src = e.target.result;
    document.getElementById('photo_preview_box').classList.remove('hidden');
    document.getElementById('photo_placeholder').classList.add('hidden');
    document.getElementById('revert_photo_btn').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
    }

    window.revertToOriginal = function() {
    const img = document.getElementById('photo_preview_img');
    const originalSrc = img.getAttribute('data-original');
    const input = document.getElementById('photo_input');

    input.value = '';

    if (originalSrc) {
    img.src = originalSrc;
    document.getElementById('photo_preview_box').classList.remove('hidden');
    document.getElementById('photo_placeholder').classList.add('hidden');
    } else {
    img.src = '';
    document.getElementById('photo_preview_box').classList.add('hidden');
    document.getElementById('photo_placeholder').classList.remove('hidden');
    }
    document.getElementById('revert_photo_btn').classList.add('hidden');
    }
    </script>
@endsection
