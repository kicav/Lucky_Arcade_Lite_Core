@extends('layouts.app')
@section('title','Game lobby')
@section('content')
<div class="page-head"><div><span class="eyebrow">GAME LOBBY</span><h1>Choose your game</h1><p>Every round is generated server-side and can be verified after rotating your seed.</p></div><div class="balance-card"><span>Balance</span><strong>{{ number_format(auth()->user()->wallet->balance) }}</strong><small>credits</small></div></div>
<div class="game-grid">@foreach($games as $game)@php($icon=match($game->code){'dice'=>'⚄','roulette'=>'◉','coinflip'=>'H/T','slots'=>'777',default=>'◆'})<article class="game-card game-{{ $game->code }}"><div class="game-icon">{{ $icon }}</div><div><h2>{{ $game->name }}</h2><p>{{ $game->description }}</p></div><span>Engine v{{ $game->activeRuleset?->engine_version }} · {{ number_format($game->min_bet) }}–{{ number_format($game->max_bet) }}</span><a class="button" href="{{ route('games.show',$game) }}">Play</a></article>@endforeach</div>
@endsection
