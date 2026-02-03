@extends('layouts.app')

@section('content')
    <div class="page-header mb-3">
        <h1 class="mb-0">Pengaturan Sistem</h1>
        <p class="text-secondary">Konfigurasi preferensi global untuk aplikasi inventaris</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 mb-3 slide-in-down">
            <svg class="icon icon-sm">
                <use href="#icon-check"></use>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div style="max-width: 800px;">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-3">
                <div class="card-header" style="border: none; padding-bottom: var(--spacing-sm);">
                    <h3 class="card-title">Informasi Umum</h3>
                </div>

                <div class="form-group mb-4">
                    <label>Foto Gereja / Organisasi</label>
                    <div id="church_photo_container" class="mb-2">
                        <!-- Preview Box -->
                        <div id="church_preview_box"
                            style="width: 100%; height: 200px; background: var(--color-bg-secondary); border-radius: 12px; border: 2px solid var(--color-border); overflow: hidden; position: relative; {{ $settings->church_photo_path ? '' : 'display: none;' }}">
                            <img id="church_preview_img"
                                src="{{ $settings->church_photo_path ? asset('storage/' . $settings->church_photo_path) : '' }}"
                                data-original="{{ $settings->church_photo_path ? asset('storage/' . $settings->church_photo_path) : '' }}"
                                style="width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" onclick="clearChurchPreview()" id="church_clear_btn"
                                style="position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; border-radius: 50%; background: var(--color-danger); color: white; border: 2px solid white; cursor: pointer; display: {{ $settings->church_photo_path ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-size: 18px; box-shadow: var(--shadow-md);">
                                ×
                            </button>
                        </div>

                        <!-- Placeholder when no photo -->
                        <div id="church_placeholder"
                            style="width: 100%; height: 200px; background: var(--color-bg-secondary); border-radius: 12px; border: 2px dashed var(--color-border); display: {{ $settings->church_photo_path ? 'none' : 'flex' }}; align-items: center; justify-content: center; color: var(--color-text-muted); flex-direction: column; gap: 0.5rem;">
                            <svg class="icon" style="width: 48px; height: 48px;">
                                <use href="#icon-image"></use>
                            </svg>
                            <span style="font-size: 0.9rem; font-weight: 500;">Klik tombol di bawah untuk pilih foto</span>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <input type="file" name="church_photo" id="church_photo_input" accept="image/*"
                            style="border: 1px dashed var(--color-border); padding: 0.75rem; border-radius: 8px; width: 100%;"
                            onchange="previewChurchPhoto(this)">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <small class="text-muted">Format: JPG, PNG, WebP. Maks: 2MB.</small>
                            <button type="button" id="revert_church_btn" onclick="revertChurchToOriginal()"
                                style="display: none; font-size: 0.8rem; color: var(--color-accent); background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline; font-weight: 500;">
                                Batalkan perubahan foto
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Nama Organisasi / Gereja</label>
                    <input type="text" name="nama_gereja" value="{{ $settings->nama_gereja ?? 'Inventaris Management' }}"
                        placeholder="Nama organisasi...">
                </div>



                <div class="form-group mb-0">
                    <label>Jumlah Baris per Tabel</label>
                    <select name="default_pagination">
                        @foreach ([10, 15, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}"
                                {{ ($settings->default_pagination ?? 15) == $opt ? 'selected' : '' }}>{{ $opt }}
                                Baris</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header" style="border: none; padding-bottom: var(--spacing-sm);">
                    <h3 class="card-title">Jadwal Perawatan</h3>
                </div>

                <div class="form-group mb-0">
                    <label>Peringatan Sebelum Jatuh Tempo (Hari)</label>
                    <input type="number" name="maintenance_threshold"
                        value="{{ $settings->maintenance_threshold ?? 30 }}">
                    <small class="text-muted">Barang akan muncul di tab "Akan Datang" sesuai jumlah hari ini.</small>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header" style="border: none; padding-bottom: var(--spacing-sm);">
                    <h3 class="card-title">Pengaturan QR Code</h3>
                </div>

                <div class="form-group mb-2">
                    <label class="flex items-center gap-2" style="cursor: pointer; display: flex; align-items: center;">
                        <input type="checkbox" name="auto_download_after_add" value="1"
                            {{ $settings->auto_download_after_add ?? true ? 'checked' : '' }}
                            style="width: auto; margin: 0;">
                        <span style="font-weight: 400;">Otomatis unduh QR setelah menambah barang baru</span>
                    </label>
                </div>

                <div class="form-group mb-0">
                    <label class="flex items-center gap-2" style="cursor: pointer; display: flex; align-items: center;">
                        <input type="checkbox" name="auto_download_after_edit" value="1"
                            {{ $settings->auto_download_after_edit ?? false ? 'checked' : '' }}
                            style="width: auto; margin: 0;">
                        <span style="font-weight: 400;">Otomatis unduh QR setelah mengubah data barang</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <svg class="icon icon-sm">
                        <use href="#icon-save"></use>
                    </svg>
                    Simpan Perubahan
                </button>
                <a href="{{ url('/') }}" wire:navigate class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
@section('scripts')
    <script>
        function previewChurchPhoto(input) {
            const previewBox = document.getElementById('church_preview_box');
            const previewImg = document.getElementById('church_preview_img');
            const placeholder = document.getElementById('church_placeholder');
            const clearBtn = document.getElementById('church_clear_btn');
            const revertBtn = document.getElementById('revert_church_btn');

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

        function clearChurchPreview() {
            const input = document.getElementById('church_photo_input');
            input.value = '';
            revertChurchToOriginal();
        }

        function revertChurchToOriginal() {
            const previewBox = document.getElementById('church_preview_box');
            const previewImg = document.getElementById('church_preview_img');
            const placeholder = document.getElementById('church_placeholder');
            const input = document.getElementById('church_photo_input');
            const clearBtn = document.getElementById('church_clear_btn');
            const revertBtn = document.getElementById('revert_church_btn');

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
@endsection
