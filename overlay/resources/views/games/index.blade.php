@extends('layouts.app')
@section('title','Game lobby')
@section('body-class','lobby-visual')
@section('content')
<div class="page-head visual-page-head"><div><span class="eyebrow"><i></i> GAME LOBBY</span><h1>Choose your game</h1><p>Four focused experiences. Every round is settled server-side and can be verified after rotating your seed.</p></div><div class="balance-card visual-balance"><span>Available balance</span><strong>{{ number_format(auth()->user()->wallet->balance) }}</strong><small>virtual credits</small></div></div>
<div class="visual-game-grid lobby-grid">
@foreach($games as $game)
    @php($asset=match($game->code){'dice'=>'lobby-dice.svg','roulette'=>'lobby-roulette.svg','coinflip'=>'lobby-coin.svg','slots'=>'lobby-slots.svg',default=>'brand-mark.svg'})
    <article class="visual-game-card game-{{ $game->code }}" data-tilt-card>
        <div class="game-art"><img src="/assets/visual/{{ $asset }}" alt="{{ $game->name }} visual"></div>
        <div class="game-card-copy"><span class="game-kicker">ENGINE v{{ $game->activeRuleset?->engine_version }}</span><h2>{{ $game->name }}</h2><p>{{ $game->description }}</p></div>
        <div class="game-card-meta"><span>{{ number_format($game->min_bet) }}–{{ number_format($game->max_bet) }}</span><a class="card-play" href="{{ route('games.show',$game) }}">Enter game <b>→</b></a></div>
    </article>
@endforeach
</div>
<div class="lobby-note"><span class="status-light"></span><div><strong>Visuals never influence results.</strong><p>The backend settles each round first; the browser only animates the stored outcome.</p></div></div>
@endsection
