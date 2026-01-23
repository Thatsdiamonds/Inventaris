<h2>Laporan Inventaris</h2>

<form method="POST" action="{{ route('reports.inventory.generate') }}">
@csrf

<label>Scope Laporan</label>
<select name="scope" id="scope" class="form-control" required>
    <option value="">-- pilih --</option>
    <option value="lokasi">Lokasi</option>
    <option value="kategori">Kategori</option>
    <option value="barang">Barang</option>
</select>

<hr>

{{-- SCOPE LOKASI --}}
<div id="lokasi" class="scope">
    <label>Lokasi</label><br>
    @foreach($locations as $l)
        <label>
            <input type="checkbox" name="locations[]" value="{{ $l->id }}">
            {{ $l->name }}
        </label><br>
    @endforeach
</div>

{{-- SCOPE KATEGORI --}}
<div id="kategori" class="scope">
    <label>Kategori</label><br>
    @foreach($categories as $c)
        <label>
            <input type="checkbox" name="categories[]" value="{{ $c->id }}">
            {{ $c->name }}
        </label><br>
    @endforeach
</div>

{{-- SCOPE BARANG --}}
<div id="barang" class="scope">
    <label>Barang</label>
    <select name="item_id" class="form-control">
        <option value="">-- pilih barang --</option>
        @foreach($items as $i)
            <option value="{{ $i->id }}">{{ $i->name }}</option>
        @endforeach
    </select>
</div>

<hr>

<label>Kondisi</label>
<select name="condition" class="form-control">
    <option value="all">Semua</option>
    <option value="Baik">Baik</option>
    <option value="Rusak">Rusak</option>
    <option value="Perbaikan">Perbaikan</option>
</select>

<hr>

<label>Periode</label>
<div class="row">
    <div class="col">
        <label>Dari</label>
        <input type="date" name="from" class="form-control">
    </div>
    <div class="col">
        <label>Sampai</label>
        <input type="date" name="to" class="form-control">
    </div>
</div>

<hr>
<a href="{{ route('reports.layout.edit', 'inventory') }}"
   class="btn btn-secondary mt-2">
   Atur Konten Laporan Inventaris
</a>
<button type="submit" class="btn btn-primary mt-3">
    Generate PDF
</button>

</form>

<script>
document.querySelectorAll('.scope').forEach(e => e.style.display='none');

document.getElementById('scope').addEventListener('change', function(){
    document.querySelectorAll('.scope').forEach(e => e.style.display='none');
    if(this.value){
        document.getElementById(this.value).style.display='block';
    }
});
</script>
