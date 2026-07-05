@extends('layouts.app')
@section('title', 'Provably fair')
@section('content')
<span class="eyebrow">TRANSPARENCY</span><h1>Provably fair seeds</h1>
<p>The hash is visible before play. Each play also stores an immutable engine version and rules checksum. Rotate the seed to reveal the old server seed, then verify historical results independently.</p>

@if(session('verification'))
    @php($verification = session('verification'))
    <section class="verification-card {{ $verification['verified'] ? 'verified' : 'failed' }}">
        <div>
            <span class="eyebrow">VERIFICATION RESULT</span>
            <h2>{{ $verification['verified'] ? 'Result verified successfully' : 'Verification failed or seed unavailable' }}</h2>
            @if(isset($verification['reason']))
                <p>{{ $verification['reason'] }}</p>
            @else
                <p>Engine v{{ $verification['engine_version'] ?? 'legacy' }} · Seed hash: {{ $verification['hash_matches'] ? 'match' : 'mismatch' }} · Rules: {{ ($verification['rules_snapshot_matches'] ?? true) && ($verification['engine_rules_match'] ?? true) ? 'match' : 'mismatch' }} · Result: {{ $verification['result_matches'] ? 'match' : 'mismatch' }} · Payout: {{ $verification['payout_matches'] ? 'match' : 'mismatch' }}</p>
            @endif
        </div>
        <span class="status-pill">{{ $verification['verified'] ? 'Verified' : 'Not verified' }}</span>
    </section>
@endif

<div class="grid two">
<section class="panel">
    <h2>Active seed</h2>
    <dl class="details">
        <dt>Server seed hash</dt><dd><code>{{ $activeSeed->server_seed_hash }}</code></dd>
        <dt>Client seed</dt><dd><code>{{ $activeSeed->client_seed }}</code></dd>
        <dt>Next nonce</dt><dd>{{ $activeSeed->nonce }}</dd>
    </dl>
    <form method="post" action="{{ route('fairness.rotate') }}" class="stack compact">
        @csrf
        <label>Optional new client seed<input type="text" name="client_seed" minlength="8" maxlength="64"></label>
        <button class="button secondary" type="submit">Reveal old seed and rotate</button>
    </form>
</section>

<section class="panel">
    <h2>Verify a historical play</h2>
    <p class="hint">A play becomes verifiable after its server seed has been rotated and revealed.</p>
    <form method="post" action="{{ route('fairness.verify') }}" class="stack compact">
        @csrf
        <label>Historical play
            <select name="entry_id" required>
                <option value="">Choose a revealed play</option>
                @foreach($verifiableEntries as $entry)
                    <option value="{{ $entry->id }}">#{{ $entry->id }} · {{ $entry->game->name }} v{{ $entry->engine_version ?? 'legacy' }} · nonce {{ $entry->nonce }} · {{ $entry->created_at->format('Y-m-d H:i') }}</option>
                @endforeach
            </select>
        </label>
        <button class="button" type="submit">Recompute and verify</button>
    </form>
    @if($verifiableEntries->isEmpty())
        <p class="empty-note">Play at least once, then rotate the seed to unlock verification.</p>
    @endif
</section>
</div>

<section class="panel">
    <h2>Revealed seeds</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Hash</th><th>Revealed server seed</th><th>Final nonce</th><th>Revealed</th></tr></thead>
        <tbody>
        @forelse($oldSeeds as $seed)
            <tr><td><code>{{ $seed->server_seed_hash }}</code></td><td><code>{{ $seed->revealed_server_seed }}</code></td><td>{{ $seed->nonce }}</td><td>{{ optional($seed->revealed_at)->format('Y-m-d H:i') }}</td></tr>
        @empty
            <tr><td colspan="4">No seed has been rotated yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
