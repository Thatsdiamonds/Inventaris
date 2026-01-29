@extends('layouts.app')

@section('content')
    <meta http-equiv="refresh" content="5">
    <style>
        .task-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }

        .progress-container {
            height: 10px;
            background: #eee;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #1890ff;
            transition: width 0.3s;
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.85em;
            text-transform: uppercase;
        }

        .status-running {
            background: #e6f7ff;
            color: #1890ff;
        }

        .status-completed {
            background: #f6ffed;
            color: #52c41a;
        }

        .status-failed {
            background: #fff1f0;
            color: #f5222d;
        }

        .status-pending {
            background: #fafafa;
            color: #666;
        }
    </style>

    <div style="display:flex; justify-content: space-between; align-items: center;">
        <h1>Aktivitas Sistem (Latar Belakang)</h1>
    </div>
    <hr>

    <p><small>* Halaman ini otomatis memuat ulang setiap 5 detik untuk memantau progres.</small></p>

    @forelse($tasks as $task)
        <div class="task-card">
            <div style="display:flex; justify-content: space-between;">
                <strong>{{ $task->name }}</strong>
                <span class="status-badge status-{{ $task->status }}">{{ $task->status }}</span>
            </div>

            @php
                $percentage = $task->total_items > 0 ? round(($task->processed_items / $task->total_items) * 100) : 0;
            @endphp

            <div class="progress-container">
                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
            </div>

            <div style="display:flex; justify-content: space-between; font-size: 0.9em; color: #666;">
                <span>Progres: {{ $task->processed_items }} / {{ $task->total_items }} barang
                    ({{ $percentage }}%)
                </span>
                <span>Dibuat: {{ $task->created_at->format('d/m/Y H:i') }}</span>
            </div>

            @if ($task->status === 'failed')
                <div
                    style="margin-top:10px; color: #f5222d; font-size: 0.9em; border-top: 1px dashed #ffa39e; padding-top: 5px;">
                    Error: {{ $task->error_message }}
                </div>
            @endif
        </div>
    @empty
        <p style="color: #666; text-align: center; padding: 40px; background: #fafafa; border-radius: 8px;">Tidak ada
            aktivitas latar belakang saat ini.</p>
    @endforelse
@endsection
