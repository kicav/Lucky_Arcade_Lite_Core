@extends('layouts.app')
@section('title', 'Dice')
@section('content')
@php($gameResult = session('game_result'))
<div class="page-head">
    <div><span class="eyebrow">PROVABLY FAIR</span><h1>Dice</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage dice-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}">
    <div class="dice-orbit">
        <div class="big-die js-dice" data-final-value="{{ data_get($gameResult, 'result.roll', '—') }}">{{ data_get($gameResult, 'result.roll', '⚄') }}</div>
    </div>
    <h2>{{ $gameResult ? (($gameResult['won'] ?? false) ? 'Winner' : 'Try again') : 'Ready to roll' }}</h2>
    <p>Result is generated on the server from your active fairness seed.</p>
</section>
<section class="panel">
    <form method="post" action="{{ route('games.dice.play', $game) }}" class="stack js-play-form">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <label>Direction<select name="direction"><option value="under" @selected(old('direction') === 'under')>Roll under</option><option value="over" @selected(old('direction') === 'over')>Roll over</option></select></label>
        <label>Target<input type="number" name="target" min="2" max="98" value="{{ old('target', 50) }}" required></label>
        <button class="button" type="submit" data-loading-text="Rolling…">Roll dice</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
