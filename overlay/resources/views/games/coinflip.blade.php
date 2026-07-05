@extends('layouts.app')
@section('title', 'Coin Flip')
@section('body-class','game-visual-page coin-page')
@section('content')
@php($gameResult = session('game_result'))
<div class="page-head visual-page-head compact-head">
    <div><a class="back-link" href="{{ route('games.index') }}">← Game lobby</a><span class="eyebrow"><i></i> QUICK PLAY</span><h1>Coin Flip</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance-card visual-balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong><small>credits</small></div>
</div>
<div class="visual-game-layout">
<section class="visual-game-stage coin-visual-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}" data-visual-game="coinflip" data-result-ready="{{ $gameResult ? '1' : '0' }}" data-won="{{ data_get($gameResult,'won',false) ? '1' : '0' }}" data-payout="{{ data_get($gameResult,'payout',0) }}">
    <div class="coin-rings" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="visual-coin-wrap">
        <div class="visual-coin" data-visual-coin data-side="{{ data_get($gameResult, 'result.side', 'heads') }}" aria-label="Coin result {{ data_get($gameResult,'result.side','not flipped') }}">
            <div class="coin-face coin-heads"><span>H</span><small>LUCKY</small></div>
            <div class="coin-face coin-tails"><span>T</span><small>ARCADE</small></div>
        </div>
        <div class="coin-shadow"></div>
    </div>
    <div class="stage-result">
        <span class="result-chip">{{ $gameResult ? strtoupper(data_get($gameResult,'result.side')) : 'READY' }}</span>
        <h2>{{ $gameResult ? (($gameResult['won'] ?? false) ? 'Your side landed' : 'Opposite side') : 'Choose heads or tails' }}</h2>
        <p>{{ $gameResult ? 'Payout: '.number_format(data_get($gameResult,'payout',0)).' credits.' : 'A fast 1.98× round with a fully verifiable outcome.' }}</p>
    </div>
</section>
<section class="visual-control-panel">
    <div class="panel-title"><div><span>COIN CONTROL</span><h2>Call the side</h2></div><span class="status-pill"><i></i> Coin ready</span></div>
    <form method="post" action="{{ route('games.coinflip.play', $game) }}" class="stack js-play-form" data-game-submit="coinflip">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<div class="input-shell"><input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required><span>credits</span></div></label>
        <div class="choice-grid two-choice" data-choice-group>
            <label class="visual-choice coin-choice"><input type="radio" name="selection" value="heads" @checked(old('selection','heads') === 'heads')><span><b>H</b> Heads</span></label>
            <label class="visual-choice coin-choice"><input type="radio" name="selection" value="tails" @checked(old('selection') === 'tails')><span><b>T</b> Tails</span></label>
        </div>
        <button class="button button-glow button-wide" type="submit" data-loading-text="Flipping…">Flip coin <span>↻</span></button>
    </form>
    <details class="fairness-drawer"><summary>Round fairness data</summary><div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div></details>
</section>
</div>
@endsection
