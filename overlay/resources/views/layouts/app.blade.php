<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lucky Arcade Lite')</title>
    <link rel="stylesheet" href="/css/app.css?v=lite-1">
    <script src="/js/app.js?v=lite-1" defer></script>
</head>
<body>
<header class="site-header">
    <div class="nav-shell">
        <a class="brand" href="{{ route('home') }}"><span>◆</span> Lucky Arcade <small>LITE</small></a>
        <button class="nav-toggle" type="button" aria-label="Open navigation" data-nav-toggle>☰</button>
        <nav data-nav>
            @auth
                <a href="{{ route('games.index') }}" @class(['active' => request()->routeIs('games.*')])>Games</a>
                <a href="{{ route('history') }}" @class(['active' => request()->routeIs('history')])>History</a>
                <a href="{{ route('fairness.show') }}" @class(['active' => request()->routeIs('fairness.*')])>Fairness</a>
                <a href="{{ route('account.show') }}" @class(['active' => request()->routeIs('account.*','security.*')])>Account</a>
                @if(auth()->user()->is_admin)<a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.*')])>Admin</a>@endif
                <span class="nav-balance">{{ number_format(auth()->user()->wallet?->balance ?? 0) }} credits</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-link" type="submit">Logout</button></form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a class="nav-cta" href="{{ route('register') }}">Create account</a>
            @endauth
        </nav>
    </div>
</header>
<main class="container">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if(session('result'))<div class="alert success">{{ session('result') }}</div>@endif
    @if($errors->any())<div class="alert error"><strong>Please check:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer>Virtual credits only · no deposits, withdrawals or cash value.</footer>
</body>
</html>
