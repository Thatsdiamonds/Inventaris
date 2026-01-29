<h2>Atur Konten Laporan {{ strtoupper($type) }}</h2>

@php
    $safeFields = is_array($fields) ? $fields : json_decode($fields, true);
    if (!is_array($safeFields)) {
        $safeFields = [];
    }
@endphp

<div style="display:flex; flex-direction: column; gap:20px">

    <div style="display:flex; gap:40px">
        {{-- FIELD TERSEDIA --}}
        <div style="width:300px">
            <h4>Field tersedia</h4>
            <div id="fields" style="min-height:100px; border:1px solid #ddd; padding:10px; background:#fafafa;">
                @foreach ($typeFields as $f)
                    @if (!in_array($f['key'], $safeFields))
                        <div class="field" data-key="{{ $f['key'] }}">{{ $f['label'] }}</div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- URUTAN KOLOM / KONTEN --}}
        <div style="flex:1">
            <h4>Urutan konten (Drag & Drop)</h4>
            <form id="layoutForm" method="POST" action="{{ route('reports.layout.save', $type) }}">
                @csrf
                <div id="layout"
                    style="min-height:100px; border:2px dashed #4a90e2; padding:10px; background:#f0f7ff;">
                    @foreach ($safeFields as $field)
                        @php
                            $fInfo = collect($typeFields)->firstWhere('key', $field);
                            $label = $fInfo ? $fInfo['label'] : strtoupper(str_replace(['.', '_'], ' ', $field));
                        @endphp
                        <div class="field" data-key="{{ $field }}">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>

                <input type="hidden" name="columns" id="columns_input">

                <div style="margin-top:20px;">
                    <h4>Kustomisasi CSS (Opsional)</h4>
                    <textarea name="css" style="width:100%; height:200px; font-family:monospace; border:1px solid #ccc; padding:10px;"
                        placeholder="/* Tambahkan CSS kustom di sini. Contoh: .label { border: 2px solid black; } */">{{ $layout->css ?? '' }}</textarea>
                </div>

                <br>
                <button type="submit"
                    style="padding:10px 20px; background:#4a90e2; color:white; border:none; border-radius:4px; cursor:pointer;">Simpan
                    Pengaturan</button>
            </form>
        </div>
    </div>
</div>

<style>
    .field {
        padding: 10px;
        margin: 6px 0;
        border: 1px solid #ccc;
        cursor: grab;
        background: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
    }

    .field:active {
        cursor: grabbing;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    new Sortable(document.getElementById('fields'), {
        group: 'shared',
        animation: 150
    });

    new Sortable(document.getElementById('layout'), {
        group: 'shared',
        animation: 150
    });

    document.getElementById('layoutForm').addEventListener('submit', function() {
        let cols = [];
        document.querySelectorAll('#layout .field').forEach(el => {
            cols.push(el.dataset.key);
        });
        document.getElementById('columns_input').value = JSON.stringify(cols);
    });
</script>
