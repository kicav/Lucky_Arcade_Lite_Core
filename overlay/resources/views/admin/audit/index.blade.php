@extends('layouts.app')
@section('title', 'Admin audit log')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMINISTRATION</span><h1>Audit log</h1></div><a class="button secondary" href="{{ route('admin.dashboard') }}">Overview</a></div>
<section class="panel">
<div class="table-wrap"><table>
<thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Subject</th><th>Before</th><th>After</th><th>IP</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr><td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td><td>{{ $log->actor?->email ?? 'system' }}</td><td>{{ $log->action }}</td><td>{{ class_basename((string) $log->subject_type) }} #{{ $log->subject_id }}</td><td><code>{{ json_encode($log->before) }}</code></td><td><code>{{ json_encode($log->after) }}</code></td><td>{{ $log->ip_address }}</td></tr>
@empty
<tr><td colspan="7">No audit records yet.</td></tr>
@endforelse
</tbody></table></div>
@if($logs->hasPages())
<div class="pagination">
    @if($logs->onFirstPage())
        <span class="button secondary disabled">Previous</span>
    @else
        <a class="button secondary" href="{{ $logs->previousPageUrl() }}">Previous</a>
    @endif
    <span class="page-indicator">Page {{ $logs->currentPage() }}</span>
    @if($logs->hasMorePages())
        <a class="button secondary" href="{{ $logs->nextPageUrl() }}">Next</a>
    @else
        <span class="button secondary disabled">Next</span>
    @endif
</div>
@endif
</section>
@endsection
