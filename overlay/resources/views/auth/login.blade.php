@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="auth-card">
    <h1>Login</h1>
    <form method="post" action="{{ route('login.store') }}" class="stack">
        @csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password<input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="button" type="submit">Login</button>
    </form>
</div>
@endsection
