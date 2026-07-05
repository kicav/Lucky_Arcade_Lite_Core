@extends('layouts.app')
@section('title', 'Two-factor challenge')
@section('content')
<section class="auth-card">
    <span class="eyebrow">SECURITY CHECK</span>
    <h1>Two-factor authentication</h1>
    <p class="hint">Enter the six-digit code from your authenticator app, or use one unused recovery code.</p>
    <form method="post" action="{{ route('two-factor.challenge.store') }}" class="stack">
        @csrf
        <label>Authenticator or recovery code
            <input name="code" autocomplete="one-time-code" autofocus required>
        </label>
        <button class="button" type="submit">Verify and sign in</button>
    </form>
    <form method="post" action="{{ route('two-factor.challenge.cancel') }}" class="delete-row">
        @csrf
        <button class="button secondary" type="submit">Cancel login</button>
    </form>
</section>
@endsection
