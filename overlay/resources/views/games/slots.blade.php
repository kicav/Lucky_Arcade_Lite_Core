@extends('layouts.app')
@section('title', 'Lucky Slots')
@section('body-class','game-visual-page slots-page')
@section('content')
@php($gameResult = session('game_result'))
@php($symbols = data_get($gameResult, 'result.symbols', ['cherry', 'lemon', 'bell']))
@php($symbolIcons = ['cherry' => '🍒', 'lemon' => '🍋', 'bell' => '🔔', 'star' => '⭐', 'seven' => '7'])
<div class="page-head visual-page-head compact-head">
    <div><a class="back-link" href="{{ route('games.index') }}">← Game lobby</a><span class="eyebrow"><i></i> BALANCED PAYTABLE</span><h1>Lucky Slots</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance-card visual-balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong><small>credits</small></div>
</div>
<div class="visual-game-layout">
<section class="visual-game-stage slots-visual-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}" data-visual-game="slots" data-result-ready="{{ $gameResult ? '1' : '0' }}" data-won="{{ data_get($gameResult,'won',false) ? '1' : '0' }}" data-payout="{{ data_get($gameResult,'payout',0) }}">
    <div class="slots-marquee"><span>LUCKY</span><i></i><span>ARCADE</span></div>
    <div class="visual-slot-machine" data-visual-slots>
        <div class="slot-top-light"></div>
        <div class="reel-window">
            @foreach($symbols as $symbol)
                <div class="visual-reel" data-final-symbol="{{ $symbol }}"><div class="reel-symbol" data-reel-symbol>{{ $symbolIcons[$symbol] ?? '◆' }}</div><small data-reel-label>{{ strtoupper($symbol) }}</small></div>
            @endforeach
            <div class="payline" aria-hidden="true"></div>
        </div>
        <div class="slot-console"><span>PAYTABLE v2</span><div class="slot-lights"><i></i><i></i><i></i><i></i><i></i></div><b>{{ $gameResult ? data_get($gameResult,'result.multiplier',0).'×' : 'READY' }}</b></div>
    </div>
    <div class="stage-result">
        <span class="result-chip">{{ $gameResult ? strtoupper(data_get($gameResult,'result.match','none')) : 'READY' }}</span>
        <h2>{{ $gameResult ? (($gameResult['won'] ?? false) ? data_get($gameResult,'result.multiplier').'× payout' : 'No matching line') : 'Spin three reels' }}</h2>
        <p>{{ $gameResult ? 'Payout: '.number_format(data_get($gameResult,'payout',0)).' credits.' : 'Pairs pay 1.25×. Triples pay between 3× and 20×.' }}</p>
    </div>
</section>
<section class="visual-control-panel">
    <div class="panel-title"><div><span>SLOT CONTROL</span><h2>Set your stake</h2></div><span class="status-pill"><i></i> Reels ready</span></div>
    <form method="post" action="{{ route('games.slots.play', $game) }}" class="stack js-play-form" data-game-submit="slots">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<div class="input-shell"><input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required><span>credits</span></div></label>
        <div class="quick-stakes" data-quick-stakes><button type="button" data-stake="10">10</button><button type="button" data-stake="25">25</button><button type="button" data-stake="50">50</button><button type="button" data-stake="100">100</button></div>
        <button class="button button-glow button-wide slots-spin-button" type="submit" data-loading-text="Spinning reels…">Spin reels <span>777</span></button>
    </form>
    <details class="fairness-drawer"><summary>Round fairness data</summary><div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div></details>
</section>
</div>
@endsection
