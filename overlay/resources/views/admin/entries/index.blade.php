@extends('layouts.app')
@section('title', 'Admin play history')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMINISTRATION</span><h1>Play history</h1></div><a class="button secondary" href="{{ route('admin.dashboard') }}">Overview</a></div>
<section class="panel">
<form method="get" class="filter-grid">
    <label>Email<input type="search" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="player@example.com"></label>
    <label>Game<select name="game"><option value="">All games</option>@foreach($games as $game)<option value="{{ $game->code }}" @selected(($filters['game'] ?? '') === $game->code)>{{ $game->name }}</option>@endforeach</select></label>
    <label>Outcome<select name="outcome"><option value="">All outcomes</option><option value="win" @selected(($filters['outcome'] ?? '') === 'win')>Win</option><option value="loss" @selected(($filters['outcome'] ?? '') === 'loss')>Loss</option></select></label>
    <div class="filter-action"><button class="button" type="submit">Filter</button></div>
</form>
</section>
<section class="panel">
<div class="table-wrap"><table>
<thead><tr><th>ID</th><th>User</th><th>Game</th><th>Version</th><th>Stake</th><th>Payout</th><th>Net</th><th>Nonce</th><th>Created</th></tr></thead>
<tbody>
@forelse($entries as $entry)
<tr><td>#{{ $entry->id }}</td><td>{{ $entry->user->email }}</td><td>{{ $entry->game->name }}</td><td><code>{{ $entry->engine_version ?? 'legacy' }}</code></td><td>{{ number_format($entry->stake) }}</td><td>{{ number_format($entry->payout) }}</td><td class="{{ $entry->net >= 0 ? 'positive' : 'negative' }}">{{ number_format($entry->net) }}</td><td>{{ $entry->nonce }}</td><td>{{ $entry->created_at->format('Y-m-d H:i:s') }}</td></tr>
@empty
<tr><td colspan="9">No matching entries.</td></tr>
@endforelse
</tbody></table></div>
@if($entries->hasPages())
<div class="pagination">
    @if($entries->onFirstPage())
        <span class="button secondary disabled">Previous</span>
    @else
        <a class="button secondary" href="{{ $entries->previousPageUrl() }}">Previous</a>
    @endif
    <span class="page-indicator">Page {{ $entries->currentPage() }}</span>
    @if($entries->hasMorePages())
        <a class="button secondary" href="{{ $entries->nextPageUrl() }}">Next</a>
    @else
        <span class="button secondary disabled">Next</span>
    @endif
</div>
@endif
</section>
@endsection
