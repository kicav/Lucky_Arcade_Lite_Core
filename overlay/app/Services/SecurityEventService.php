<?php

namespace App\Services;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityEventService
{
    public function record(?User $user, string $event, Request $request, array $metadata = []): SecurityEvent
    {
        return SecurityEvent::query()->create([
            'user_id' => $user?->id,
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}
