<a href="{{ route('reports.inventory') }}" class="btn btn-success">
    Laporan Inventaris
</a>
<form action="{{ route('reports.services.generate') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-warning">
        Download Laporan Servis
    </button>
</form>




