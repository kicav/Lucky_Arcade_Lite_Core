<?php

namespace App\Services;

use App\Models\FairnessSeed;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class FairnessSeedService
{
    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function create(User $user): FairnessSeed
    {
        $serverSeed = bin2hex(random_bytes(32));

        return $user->fairnessSeeds()->create([
            'server_seed' => $serverSeed,
            'server_seed_hash' => $this->fairness->hashServerSeed($serverSeed),
            'client_seed' => Str::lower(Str::random(24)),
            'nonce' => 0,
            'active' => true,
        ]);
    }

    public function active(User $user): FairnessSeed
    {
        return $user->fairnessSeeds()->where('active', true)->first()
            ?? $this->create($user);
    }

    public function rotate(User $user, ?string $clientSeed = null): FairnessSeed
    {
        return DB::transaction(function () use ($user, $clientSeed): FairnessSeed {
            $active = $user->fairnessSeeds()
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            if ($active) {
                $active->update([
                    'active' => false,
                    'revealed_server_seed' => $active->server_seed,
                    'revealed_at' => now(),
                ]);
            }

            $new = $this->create($user);

            if ($clientSeed !== null && $clientSeed !== '') {
                $new->update(['client_seed' => $clientSeed]);
            }

            return $new->refresh();
        });
    }
}
