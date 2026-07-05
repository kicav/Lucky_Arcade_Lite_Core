@extends('layouts.app')
@section('title', 'Lucky Arcade Lite')
@section('content')
<section class="hero">
    <div class="hero-copy"><span class="eyebrow">LITE CORE EDITION</span><h1>Four games. One clean, verifiable experience.</h1><p>Fast virtual-credit games with an immutable wallet ledger, versioned rules and provably-fair results—without the clutter.</p><div class="hero-actions">@auth<a class="button" href="{{ route('games.index') }}">Enter game lobby</a>@else<a class="button" href="{{ route('register') }}">Start with 10,000 credits</a><a class="button secondary" href="{{ route('login') }}">Login</a>@endauth</div></div>
    <div class="hero-orb"><div><strong>4</strong><span>core games</span></div><div><strong>HMAC</strong><span>verifiable RNG</span></div><div><strong>0</strong><span>cash value</span></div></div>
</section>
<section class="section-head"><div><span class="eyebrow">GAME LOBBY</span><h2>Simple by design</h2></div></section>
<div class="game-grid">@foreach($games as $game)@php($icon=match($game->code){'dice'=>'⚄','roulette'=>'◉','coinflip'=>'H/T','slots'=>'777',default=>'◆'})<article class="game-card game-{{ $game->code }}"><div class="game-icon">{{ $icon }}</div><div><h3>{{ $game->name }}</h3><p>{{ $game->description }}</p></div><span>{{ number_format($game->min_bet) }}–{{ number_format($game->max_bet) }} credits</span>@auth<a class="card-link" href="{{ route('games.show',$game) }}">Play now →</a>@endauth</article>@endforeach</div>
@endsection
