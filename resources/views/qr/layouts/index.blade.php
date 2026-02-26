@extends('layouts.app')

@section('content')
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
                <h1 class="page-title">Sistem Layout Label QR</h1>
                <p class="text-secondary">Kelola tata letak label QR untuk pencetakan yang presisi.</p>
            </div>
            <div class="col-md-6 col-sm-12 text-md-end">
                <a href="{{ route('label-layouts.create') }}" class="btn btn-primary">
                    <svg class="icon icon-sm me-2">
                        <use href="#icon-plus"></use>
                    </svg> Buat Layout Baru
                </a>
                <a href="{{ route('qr.index') }}" class="btn btn-ghost ms-2">Kembali</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter table-mobile-md card-table">
                <thead>
                    <tr>
                        <th>Nama Layout</th>
                        <th>Ukuran Label (mm)</th>
                        <th>Ukuran Kertas</th>
                        <th>Margin (mm)</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($layouts as $layout)
                        <tr>
                            <td>
                                <div class="font-weight-medium">{{ $layout->name }}</div>
                                <div class="text-muted text-sm">{{ $layout->font_size }}pt font</div>
                            </td>
                            <td>{{ $layout->width }} x {{ $layout->height }}</td>
                            <td>{{ $layout->paper_size }}</td>
                            <td class="text-muted">
                                T:{{ $layout->margin_top }} B:{{ $layout->margin_bottom }} <br>
                                L:{{ $layout->margin_left }} R:{{ $layout->margin_right }}
                            </td>
                            <td>
                                @if ($layout->is_active)
                                    <span class="badge bg-success-lt">Aktif</span>
                                @else
                                    <form action="{{ route('label-layouts.activate', $layout->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-ghost-secondary">Aktifkan</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('label-layouts.edit', $layout->id) }}" class="btn btn-white btn-sm">
                                        Edit
                                    </a>
                                    @if (!$layout->is_active)
                                        <form action="{{ route('label-layouts.destroy', $layout->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-white btn-sm text-danger">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                <div class="empty-state">
                                    <div class="empty-title">Belum ada layout</div>
                                    <p class="text-secondary">Silakan buat layout terlebih dahulu.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
