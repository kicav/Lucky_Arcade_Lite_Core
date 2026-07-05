<!doctype html>
<html lang="en" data-motion="full" data-sound="off">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#080b16">
    <title>@yield('title', 'Lucky Arcade Visual')</title>
    <link rel="stylesheet" href="/css/app.css?v=visual-1.1.0">
    <script src="/js/app.js?v=visual-1.1.0" defer></script>
</head>
<body class="@yield('body-class')" data-visual-shell>
<div class="ambient" aria-hidden="true">
    <div class="ambient-orb ambient-orb-a"></div>
    <div class="ambient-orb ambient-orb-b"></div>
    <div class="ambient-grid"></div>
    <div class="ambient-particles" data-particles></div>
</div>
<canvas class="celebration-canvas" data-celebration aria-hidden="true"></canvas>
<header class="site-header">
    <div class="nav-shell">
        <a class="brand" href="{{ route('home') }}">
            <img src="/assets/visual/brand-mark.svg" alt="" width="38" height="38">
            <span class="brand-copy"><strong>Lucky Arcade</strong><small>VISUAL</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-nav-toggle>☰</button>
        <nav data-nav>
            @auth
                <a href="{{ route('games.index') }}" @class(['active' => request()->routeIs('games.*')])>Games</a>
                <a href="{{ route('history') }}" @class(['active' => request()->routeIs('history')])>History</a>
                <a href="{{ route('fairness.show') }}" @class(['active' => request()->routeIs('fairness.*')])>Fairness</a>
                <a href="{{ route('account.show') }}" @class(['active' => request()->routeIs('account.*','security.*')])>Account</a>
                @if(auth()->user()->is_admin)<a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.*')])>Admin</a>@endif
                <span class="nav-balance"><span class="balance-dot"></span><strong>{{ number_format(auth()->user()->wallet?->balance ?? 0) }}</strong> credits</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-link" type="submit">Logout</button></form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a class="nav-cta" href="{{ route('register') }}">Create account</a>
            @endauth
            <div class="visual-controls" aria-label="Visual preferences">
                <button type="button" class="icon-control" data-sound-toggle aria-pressed="false" title="Toggle sound"><span data-sound-icon>♪</span><span class="sr-only">Toggle sound</span></button>
                <button type="button" class="icon-control" data-motion-toggle aria-pressed="false" title="Reduce animation"><span data-motion-icon>◌</span><span class="sr-only">Toggle reduced motion</span></button>
            </div>
        </nav>
    </div>
</header>
<main class="container">
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if(session('result') && ! session('game_result'))<div class="alert success" role="status">{{ session('result') }}</div>@endif
    @if($errors->any())<div class="alert error" role="alert"><strong>Please check:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer><span>Virtual credits only</span><i></i><span>No deposits or withdrawals</span><i></i><span>Provably fair results</span></footer>
</body>
</html>
