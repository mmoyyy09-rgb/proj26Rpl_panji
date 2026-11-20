@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Activity Log</h3>
        <small class="text-muted">
            @if(auth()->user()->role === 'admin')
                Riwayat aktivitas seluruh pengguna.
            @else
                Riwayat aktivitas akun {{ auth()->user()->name }}.
            @endif
        </small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $activity->user?->name ?? 'Akun dihapus' }}</td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $activities->links() }}
    </div>
</div>
@endsection
