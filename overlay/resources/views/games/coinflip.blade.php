@extends('layouts.app')
@section('title', 'Coin Flip')
@section('content')
@php($gameResult = session('game_result'))
<div class="page-head">
    <div><span class="eyebrow">PROVABLY FAIR</span><h1>Coin Flip</h1><p class="hint">Engine v{{ $game->activeRuleset?->engine_version ?? 'legacy' }} · Rules {{ substr($game->activeRuleset?->checksum ?? '', 0, 12) }}…</p></div>
    <div class="balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage coin-stage {{ $gameResult ? (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss') : '' }}">
    <div class="coin js-coin {{ $gameResult ? 'coin-settled' : '' }}" data-side="{{ data_get($gameResult, 'result.side', 'heads') }}">
        <span>{{ data_get($gameResult, 'result.side', 'heads') === 'heads' ? 'H' : 'T' }}</span>
    </div>
    <h2>{{ $gameResult ? (($gameResult['won'] ?? false) ? 'Winner' : 'Try again') : 'Choose a side' }}</h2>
    <p>Heads or tails with a 1.98× virtual-credit payout.</p>
</section>
<section class="panel">
    <form method="post" action="{{ route('games.coinflip.play', $game) }}" class="stack js-play-form">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <label>Side
            <select name="selection">
                <option value="heads" @selected(old('selection') === 'heads')>Heads</option>
                <option value="tails" @selected(old('selection') === 'tails')>Tails</option>
            </select>
        </label>
        <button class="button" type="submit" data-loading-text="Flipping…">Flip coin</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
