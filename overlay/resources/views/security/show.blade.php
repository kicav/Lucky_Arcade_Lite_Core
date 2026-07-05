@extends('layouts.app')
@section('title', 'Security')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">ACCOUNT SECURITY</span><h1>Security center</h1></div>
    <div class="security-status {{ $user->hasTwoFactorEnabled() ? 'enabled' : '' }}">
        <span>Two-factor authentication</span>
        <strong>{{ $user->hasTwoFactorEnabled() ? 'Enabled' : 'Not enabled' }}</strong>
    </div>
</div>

@if($recoveryCodes)
<section class="panel recovery-panel">
    <span class="eyebrow">SAVE THESE NOW</span>
    <h2>Recovery codes</h2>
    <p class="hint">Each code works once. Store them offline. They will not be displayed again.</p>
    <div class="recovery-codes">@foreach($recoveryCodes as $code)<code>{{ $code }}</code>@endforeach</div>
</section>
@endif

@if(! $user->hasTwoFactorEnabled())
<div class="grid two">
<section class="panel">
    <h2>1. Start setup</h2>
    <p class="hint">Confirm your password before generating an authenticator secret.</p>
    <form method="post" action="{{ route('security.two-factor.begin') }}" class="stack">
        @csrf
        <label>Current password<input type="password" name="current_password" required></label>
        <button class="button" type="submit">Generate authenticator secret</button>
    </form>
</section>

<section class="panel">
    <h2>2. Confirm setup</h2>
    @if($pendingSecret)
        <p>Add this secret manually to Google Authenticator, Microsoft Authenticator, 1Password or another TOTP app.</p>
        <div class="secret-display"><code>{{ $pendingSecret }}</code></div>
        <details class="uri-details"><summary>Show otpauth URI</summary><code>{{ $otpAuthUri }}</code></details>
        <form method="post" action="{{ route('security.two-factor.confirm') }}" class="stack">
            @csrf
            <label>Six-digit code<input name="code" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" required></label>
            <button class="button" type="submit">Enable two-factor authentication</button>
        </form>
    @else
        <p class="hint">Complete step 1 to generate a temporary secret.</p>
    @endif
</section>
</div>
@else
<div class="grid two">
<section class="panel">
    <h2>Regenerate recovery codes</h2>
    <p class="hint">This invalidates every existing recovery code.</p>
    <form method="post" action="{{ route('security.recovery-codes.regenerate') }}" class="stack">
        @csrf
        <label>Current password<input type="password" name="current_password" required></label>
        <label>Authenticator code<input name="code" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" required></label>
        <button class="button secondary" type="submit">Generate new recovery codes</button>
    </form>
</section>
<section class="panel danger-zone">
    <h2>Disable two-factor authentication</h2>
    <p class="hint">Use your password and either a current authenticator code or an unused recovery code.</p>
    <form method="post" action="{{ route('security.two-factor.disable') }}" class="stack">
        @csrf @method('delete')
        <label>Current password<input type="password" name="current_password" required></label>
        <label>Authenticator or recovery code<input name="code" required></label>
        <button class="button danger" type="submit">Disable two-factor authentication</button>
    </form>
</section>
</div>
@endif

<section class="panel">
    <div class="section-head"><div><span class="eyebrow">RECENT ACTIVITY</span><h2>Security events</h2></div><span class="hint">Latest 50 events</span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Time</th><th>Event</th><th>IP address</th><th>Device</th></tr></thead>
        <tbody>
        @forelse($events as $event)
            <tr><td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td><td><code>{{ $event->event }}</code></td><td>{{ $event->ip_address ?: '—' }}</td><td class="device-cell">{{ Str::limit($event->user_agent ?: 'Unknown', 85) }}</td></tr>
        @empty
            <tr><td colspan="4">No security events recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
