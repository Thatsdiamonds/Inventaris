<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<h3>Atur Konten Laporan Inventaris</h3>

<ul id="columns" style="list-style:none;padding:0">
@foreach($layout->columns as $key)
<li data-id="{{ $key }}" style="padding:8px;border:1px solid #ccc;margin-bottom:5px;cursor:move">
    {{ $fields[$key]['label'] }}
</li>
@endforeach
</ul>

<button id="save">Simpan</button>
<script>
const sortable = new Sortable(document.getElementById('columns'), {
    animation: 150
});

document.getElementById('save').onclick = () => {
    const columns = [...document.querySelectorAll('#columns li')]
        .map(li => li.dataset.id);

    fetch("{{ route('reports.inventory.layout.update') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ columns })
    }).then(() => location.reload());
};
</script>

