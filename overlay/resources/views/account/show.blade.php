@extends('layouts.app')
@section('title', 'Account')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">ACCOUNT & PLAY CONTROLS</span><h1>Your account</h1><a class="text-link" href="{{ route('security.show') }}">Open security center →</a></div>
    <div class="balance"><span>Today&apos;s stake</span><strong>{{ number_format($todayStake) }}</strong><small>virtual credits</small></div>
</div>

<div class="grid two">
<section class="panel">
    <h2>Profile</h2>
    <form method="post" action="{{ route('account.profile.update') }}" class="stack">
        @csrf @method('put')
        <label>Name<input type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required></label>
        <label>Email<input type="email" value="{{ $user->email }}" disabled></label>
        <button class="button" type="submit">Save profile</button>
    </form>
</section>

<section class="panel">
    <h2>Change password</h2>
    <form method="post" action="{{ route('account.password.update') }}" class="stack">
        @csrf @method('put')
        <label>Current password<input type="password" name="current_password" required></label>
        <label>New password<input type="password" name="password" minlength="10" required></label>
        <label>Confirm new password<input type="password" name="password_confirmation" minlength="10" required></label>
        <button class="button secondary" type="submit">Update password</button>
    </form>
</section>
</div>

<section class="panel play-controls">
    <span class="eyebrow">RESPONSIBLE PLAY</span>
    <h2>Limits and self-exclusion</h2>
    <p class="hint">These controls apply to virtual-credit play. An active self-exclusion cannot be cancelled before its end time.</p>
    @if($user->self_excluded_until && $user->self_excluded_until->isFuture())
        <div class="alert error">Self-exclusion is active until {{ $user->self_excluded_until->format('Y-m-d H:i') }}.</div>
    @endif
    <form method="post" action="{{ route('account.controls.update') }}" class="filter-grid controls-grid">
        @csrf @method('put')
        <label>Daily stake limit
            <input type="number" name="daily_stake_limit" min="10" max="1000000" value="{{ old('daily_stake_limit', $user->daily_stake_limit) }}" placeholder="No limit">
        </label>
        <label>Start/extend self-exclusion
            <select name="self_exclusion_days">
                <option value="">No new exclusion</option>
                <option value="1">1 day</option>
                <option value="7">7 days</option>
                <option value="30">30 days</option>
            </select>
        </label>
        <div class="filter-action"><button class="button" type="submit">Update controls</button></div>
    </form>
</section>
@endsection
