@extends('layouts.app')
@section('title', 'Lucky Slots')
@section('content')
@php($gameResult = session('game_result'))
@php($symbols = data_get($gameResult, 'result.symbols', ['cherry', 'lemon', 'bell']))
@php($symbolIcons = ['cherry' => '🍒', 'lemon' => '🍋', 'bell' => '🔔', 'star' => '⭐', 'seven' => '7'])
<div class="page-head">
    <div><span class="eyebrow">PROVABLY FAIR</span><h1>Lucky Slots</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage slot-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}">
    <div class="slot-machine js-slots {{ $gameResult ? 'slots-settled' : '' }}">
        @foreach($symbols as $symbol)
            <div class="slot-reel"><span>{{ $symbolIcons[$symbol] ?? '◆' }}</span><small>{{ ucfirst($symbol) }}</small></div>
        @endforeach
    </div>
    <h2>{{ $gameResult ? (($gameResult['won'] ?? false) ? data_get($gameResult, 'result.multiplier').'× winner' : 'Spin again') : 'Three provably-fair reels' }}</h2>
    <p>Any pair pays 1.25×. Triples pay 3×–20× under the balanced v2 paytable.</p>
</section>
<section class="panel">
    <form method="post" action="{{ route('games.slots.play', $game) }}" class="stack js-play-form">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <button class="button" type="submit" data-loading-text="Spinning reels…">Spin</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
