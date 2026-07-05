@extends('layouts.app')
@section('title', 'Lucky Arcade Visual')
@section('body-class','home-visual')
@section('content')
<section class="visual-hero">
    <div class="hero-copy">
        <span class="eyebrow"><i></i> VISUAL EDITION</span>
        <h1>Arcade energy.<br><span>Transparent outcomes.</span></h1>
        <p>Four focused virtual-credit games rebuilt with animated scenes, responsive controls and verifiable server-side results.</p>
        <div class="hero-actions">
            @auth
                <a class="button button-glow" href="{{ route('games.index') }}">Enter game lobby <span>→</span></a>
            @else
                <a class="button button-glow" href="{{ route('register') }}">Start with 10,000 credits <span>→</span></a>
                <a class="button secondary" href="{{ route('login') }}">Login</a>
            @endauth
        </div>
        <div class="trust-strip">
            <div><strong>4</strong><span>focused games</span></div>
            <div><strong>HMAC</strong><span>verifiable RNG</span></div>
            <div><strong>0</strong><span>cash value</span></div>
        </div>
    </div>
    <div class="hero-showcase" data-hero-showcase aria-hidden="true">
        <div class="showcase-ring ring-one"></div><div class="showcase-ring ring-two"></div>
        <article class="showcase-card showcase-dice"><img src="/assets/visual/lobby-dice.svg" alt=""><span>DICE</span></article>
        <article class="showcase-card showcase-roulette"><img src="/assets/visual/lobby-roulette.svg" alt=""><span>ROULETTE</span></article>
        <article class="showcase-card showcase-coin"><img src="/assets/visual/lobby-coin.svg" alt=""><span>COIN FLIP</span></article>
        <article class="showcase-card showcase-slots"><img src="/assets/visual/lobby-slots.svg" alt=""><span>SLOTS</span></article>
        <div class="showcase-core"><img src="/assets/visual/brand-mark.svg" alt=""></div>
    </div>
</section>
<section class="section-head visual-section-head"><div><span class="eyebrow"><i></i> GAME LOBBY</span><h2>Pick your atmosphere</h2><p>Each game has a distinct visual scene while sharing the same secure wallet and fairness system.</p></div></section>
<div class="visual-game-grid">
@foreach($games as $game)
    @php($asset=match($game->code){'dice'=>'lobby-dice.svg','roulette'=>'lobby-roulette.svg','coinflip'=>'lobby-coin.svg','slots'=>'lobby-slots.svg',default=>'brand-mark.svg'})
    <article class="visual-game-card game-{{ $game->code }}" data-tilt-card>
        <div class="game-art"><img src="/assets/visual/{{ $asset }}" alt="{{ $game->name }} visual"></div>
        <div class="game-card-copy"><span class="game-kicker">{{ strtoupper($game->code === 'coinflip' ? 'quick play' : ($game->code === 'roulette' ? 'classic table' : 'provably fair')) }}</span><h3>{{ $game->name }}</h3><p>{{ $game->description }}</p></div>
        <div class="game-card-meta"><span>{{ number_format($game->min_bet) }}–{{ number_format($game->max_bet) }} credits</span>@auth<a class="card-play" href="{{ route('games.show',$game) }}">Play <b>→</b></a>@endauth</div>
    </article>
@endforeach
</div>
<section class="visual-principles">
    <article><span>01</span><h3>Server-side outcome</h3><p>Animations present a result already settled by the backend. They never decide it.</p></article>
    <article><span>02</span><h3>Verifiable seeds</h3><p>Reveal old seeds and independently recompute historic rounds at any time.</p></article>
    <article><span>03</span><h3>Motion controls</h3><p>Sound and animation preferences are available in the navigation bar.</p></article>
</section>
@endsection
