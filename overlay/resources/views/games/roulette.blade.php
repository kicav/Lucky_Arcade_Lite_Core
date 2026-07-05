@extends('layouts.app')
@section('title', 'European Roulette')
@section('content')
@php($gameResult = session('game_result'))
<div class="page-head">
    <div><span class="eyebrow">SINGLE ZERO</span><h1>European Roulette</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage roulette-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}">
    <div class="wheel-shell">
        <div class="wheel {{ $gameResult ? 'js-wheel' : '' }}" data-final-value="{{ data_get($gameResult, 'result.number', 0) }}"><span class="wheel-number">{{ data_get($gameResult, 'result.number', 0) }}</span></div>
        <span class="wheel-pointer">▼</span>
    </div>
    <h2>{{ $gameResult ? ucfirst(data_get($gameResult, 'result.color', 'green')).' '.data_get($gameResult, 'result.number') : 'Place a virtual-credit bet' }}</h2>
    <p>0–36. Zero is green and loses on even-money outside bets.</p>
</section>
<section class="panel">
    <form method="post" action="{{ route('games.roulette.play', $game) }}" class="stack js-play-form">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <label>Bet type
            <select name="bet_type" class="js-roulette-type">
                <option value="straight" @selected(old('bet_type', 'straight') === 'straight')>Straight number (0–36)</option>
                <option value="color" @selected(old('bet_type') === 'color')>Color</option>
                <option value="parity" @selected(old('bet_type') === 'parity')>Odd / even</option>
                <option value="range" @selected(old('bet_type') === 'range')>Low / high</option>
                <option value="dozen" @selected(old('bet_type') === 'dozen')>Dozen</option>
            </select>
        </label>
        <label>Selection<input class="js-roulette-selection" type="text" name="selection" value="{{ old('selection', '17') }}" placeholder="17" required></label>
        <p class="hint js-roulette-hint">Enter a number from 0 to 36.</p>
        <button class="button" type="submit" data-loading-text="Spinning…">Spin</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
