@extends('layouts.app')

@section('content')
    <h1>Menu Laporan</h1>

    <div style="display:flex; gap:10px; flex-wrap: wrap; margin-top: 20px;">
        <a href="{{ route('reports.inventory') }}"
            style="background:#52c41a; color:white; text-decoration:none; padding:10px 15px; border-radius:4px; display:inline-block;">
            Laporan Inventaris
        </a>

        <a href="{{ route('reports.layout.edit', 'inventory') }}"
            style="background:#6c757d; color:white; text-decoration:none; padding:10px 15px; border-radius:4px; display:inline-block;">
            Atur Layout Inventaris
        </a>

        <a href="{{ route('reports.layout.edit', 'qr') }}"
            style="background:#17a2b8; color:white; text-decoration:none; padding:10px 15px; border-radius:4px; display:inline-block;">
            Atur Layout QR Label
        </a>
    </div>

    <hr style="margin: 30px 0;">

    <form action="{{ route('reports.services.generate') }}" method="POST">
        @csrf
        <button type="submit"
            style="background:#faad14; color:white; border:none; padding:10px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">
            Download Laporan Servis
        </button>
    </form>
@endsection
