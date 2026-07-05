@extends('layouts.app')
@section('title', 'European Roulette')
@section('body-class','game-visual-page roulette-page')
@section('content')
@php($gameResult = session('game_result'))
<div class="page-head visual-page-head compact-head">
    <div><a class="back-link" href="{{ route('games.index') }}">← Game lobby</a><span class="eyebrow"><i></i> SINGLE ZERO</span><h1>European Roulette</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance-card visual-balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong><small>credits</small></div>
</div>
<div class="visual-game-layout roulette-layout">
<section class="visual-game-stage roulette-visual-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}" data-visual-game="roulette" data-result-ready="{{ $gameResult ? '1' : '0' }}" data-won="{{ data_get($gameResult,'won',false) ? '1' : '0' }}" data-payout="{{ data_get($gameResult,'payout',0) }}">
    <div class="roulette-table-glow" aria-hidden="true"></div>
    <div class="roulette-assembly">
        <span class="roulette-pointer" aria-hidden="true"></span>
        <div class="visual-roulette-wheel" data-roulette-wheel data-final-value="{{ data_get($gameResult, 'result.number', 0) }}">
            <div class="roulette-number-track" data-roulette-track></div>
            <div class="roulette-rim"></div>
            <div class="roulette-inner"><div class="roulette-ball" data-roulette-ball></div><div class="roulette-hub"><small>RESULT</small><strong data-roulette-readout>{{ $gameResult ? data_get($gameResult,'result.number') : '—' }}</strong></div></div>
        </div>
    </div>
    <div class="stage-result">
        <span class="result-chip {{ data_get($gameResult,'result.color') ? 'chip-'.data_get($gameResult,'result.color') : '' }}">{{ $gameResult ? strtoupper(data_get($gameResult,'result.color','green')) : 'TABLE OPEN' }}</span>
        <h2>{{ $gameResult ? data_get($gameResult,'result.number').' landed' : 'Place a table bet' }}</h2>
        <p>{{ $gameResult ? (($gameResult['won'] ?? false) ? 'Winning selection · '.number_format(data_get($gameResult,'payout',0)).' credits paid.' : 'The wheel settled fairly. Choose another position.') : 'Single-zero European rules with straight and outside bets.' }}</p>
    </div>
</section>
<section class="visual-control-panel">
    <div class="panel-title"><div><span>ROULETTE CONTROL</span><h2>Place your chip</h2></div><span class="status-pill"><i></i> Table open</span></div>
    <form method="post" action="{{ route('games.roulette.play', $game) }}" class="stack js-play-form" data-game-submit="roulette">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<div class="input-shell"><input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required><span>credits</span></div></label>
        <label>Bet type<select name="bet_type" class="js-roulette-type"><option value="straight" @selected(old('bet_type', 'straight') === 'straight')>Straight number (0–36)</option><option value="color" @selected(old('bet_type') === 'color')>Color</option><option value="parity" @selected(old('bet_type') === 'parity')>Odd / even</option><option value="range" @selected(old('bet_type') === 'range')>Low / high</option><option value="dozen" @selected(old('bet_type') === 'dozen')>Dozen</option></select></label>
        <label>Selection<input class="js-roulette-selection" type="text" name="selection" value="{{ old('selection', '17') }}" placeholder="17" required></label>
        <p class="hint js-roulette-hint">Enter a number from 0 to 36.</p>
        <button class="button button-glow button-wide" type="submit" data-loading-text="Spinning…">Spin wheel <span>◉</span></button>
    </form>
    <details class="fairness-drawer"><summary>Round fairness data</summary><div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div></details>
</section>
</div>
@endsection
