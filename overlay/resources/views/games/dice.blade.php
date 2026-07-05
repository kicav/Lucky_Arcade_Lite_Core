@extends('layouts.app')
@section('title', 'Dice')
@section('body-class','game-visual-page dice-page')
@section('content')
@php($gameResult = session('game_result'))
<div class="page-head visual-page-head compact-head">
    <div><a class="back-link" href="{{ route('games.index') }}">← Game lobby</a><span class="eyebrow"><i></i> PROVABLY FAIR</span><h1>Dice</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance-card visual-balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong><small>credits</small></div>
</div>
<div class="visual-game-layout">
<section class="visual-game-stage dice-visual-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}" data-visual-game="dice" data-result-ready="{{ $gameResult ? '1' : '0' }}" data-won="{{ data_get($gameResult,'won',false) ? '1' : '0' }}" data-payout="{{ data_get($gameResult,'payout',0) }}">
    <div class="stage-atmosphere" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="dice-platform">
        <div class="visual-dice-cube" data-dice-cube data-final-value="{{ data_get($gameResult, 'result.roll', '00.00') }}" aria-label="Dice result {{ data_get($gameResult, 'result.roll', 'not rolled') }}">
            <div class="cube-face face-front"><i></i><i></i><i></i><i></i></div>
            <div class="cube-face face-back"><i></i><i></i><i></i><i></i><i></i><i></i></div>
            <div class="cube-face face-right"><i></i><i></i><i></i></div>
            <div class="cube-face face-left"><i></i><i></i><i></i><i></i><i></i></div>
            <div class="cube-face face-top"><i></i></div>
            <div class="cube-face face-bottom"><i></i><i></i></div>
        </div>
        <div class="dice-readout"><span>ROLL</span><strong data-dice-readout>{{ data_get($gameResult, 'result.roll', '--.--') }}</strong></div>
    </div>
    <div class="stage-result">
        <span class="result-chip">{{ $gameResult ? (($gameResult['won'] ?? false) ? 'WIN' : 'ROUND COMPLETE') : 'READY' }}</span>
        <h2>{{ $gameResult ? (($gameResult['won'] ?? false) ? 'Perfect call' : 'Try another line') : 'Set your target' }}</h2>
        <p>{{ $gameResult ? 'Server result: '.data_get($gameResult,'result.roll').' · payout '.number_format(data_get($gameResult,'payout',0)).' credits.' : 'Choose over or under, then watch the settled result animate.' }}</p>
    </div>
</section>
<section class="visual-control-panel">
    <div class="panel-title"><div><span>DICE CONTROL</span><h2>Place a virtual bet</h2></div><span class="status-pill"><i></i> Server ready</span></div>
    <form method="post" action="{{ route('games.dice.play', $game) }}" class="stack js-play-form" data-game-submit="dice">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<div class="input-shell"><input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required><span>credits</span></div></label>
        <div class="choice-grid two-choice" data-choice-group>
            <label class="visual-choice"><input type="radio" name="direction" value="under" @checked(old('direction','under') === 'under')><span><b>↓</b> Roll under</span></label>
            <label class="visual-choice"><input type="radio" name="direction" value="over" @checked(old('direction') === 'over')><span><b>↑</b> Roll over</span></label>
        </div>
        <label>Target<div class="range-row"><input type="range" name="target" min="2" max="98" value="{{ old('target', 50) }}" data-range-output><output>{{ old('target', 50) }}</output></div></label>
        <button class="button button-glow button-wide" type="submit" data-loading-text="Rolling…">Roll dice <span>◆</span></button>
    </form>
    <details class="fairness-drawer"><summary>Round fairness data</summary><div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div></details>
</section>
</div>
@endsection
