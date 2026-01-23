<h2>Atur Konten Laporan {{ ucfirst($type) }}</h2>

<div style="display:flex; gap:40px">

    {{-- FIELD TERSEDIA --}}
    <div style="width:300px">
        <h4>Field tersedia</h4>

        <div id="fields">
            <div class="field" data-key="uqcode">Kode Barang</div>
            <div class="field" data-key="name">Nama Barang</div>
            <div class="field" data-key="category.name">Kategori</div>
            <div class="field" data-key="location.name">Lokasi</div>
            <div class="field" data-key="condition">Kondisi</div>
            <div class="field" data-key="last_service_date">Terakhir Servis</div>
        </div>
    </div>

    {{-- URUTAN KOLOM --}}
    <div style="flex:1">
        <h4>Urutan kolom laporan</h4>

        <form method="POST" action="{{ route('reports.layout.save', $type) }}">
            @csrf

            <div id="layout"
                 style="min-height:200px; border:1px dashed #aaa; padding:10px">

                @php
                    $safeFields = is_array($fields)
                        ? $fields
                        : json_decode($fields, true);
                @endphp

                @if(!empty($safeFields))
                    @foreach($safeFields as $field)
                        <div class="field" data-key="{{ $field }}">
                            {{ strtoupper(str_replace(['.', '_'], ' ', $field)) }}
                        </div>
                    @endforeach
                @endif
            </div>

            <input type="hidden" name="columns" id="columns">

            <br>
            <button type="submit">Simpan</button>
        </form>
    </div>

</div>

<style>
.field {
    padding:6px;
    margin:4px 0;
    border:1px solid #444;
    cursor:move;
    background:#f8f8f8;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
new Sortable(document.getElementById('fields'), {
    group: 'layout',
    sort: false
});

new Sortable(document.getElementById('layout'), {
    group: 'layout',
    animation: 150
});

document.querySelector('form').addEventListener('submit', function () {
    let cols = [];
    document.querySelectorAll('#layout .field').forEach(el => {
        cols.push(el.dataset.key);
    });
    document.getElementById('columns').value = JSON.stringify(cols);
});
</script>
