<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use App\Services\FairnessSeedService;
use App\Services\GameRulesetService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            ['code' => 'dice', 'name' => 'Dice', 'description' => 'Choose over or under and test a verifiable random roll.', 'config' => ['house_edge' => 0.01]],
            ['code' => 'roulette', 'name' => 'European Roulette', 'description' => 'Single-zero roulette with transparent virtual-credit payouts.', 'config' => ['variant' => 'european']],
            ['code' => 'coinflip', 'name' => 'Coin Flip', 'description' => 'Pick heads or tails in a fast provably-fair round.', 'config' => ['multiplier' => 1.98]],
            ['code' => 'slots', 'name' => 'Lucky Slots', 'description' => 'Spin three deterministic reels using the balanced v2 paytable.', 'config' => ['reels' => 3, 'paytable' => 'v2', 'theoretical_rtp_bp' => 9504]],
        ];

        foreach ($games as $definition) {
            Game::query()->updateOrCreate(['code' => $definition['code']], $definition + ['enabled' => true, 'min_bet' => 10, 'max_bet' => 1000]);
        }
        Game::query()->whereNotIn('code', Game::LITE_CODES)->update(['enabled' => false]);
        app(GameRulesetService::class)->syncAll();

        $this->seedUser('Administrator', 'admin@example.com', 'ChangeMe123!', true, 100000);
        $this->seedUser('Demo Player', 'demo@example.com', 'Demo123!', false, 10000);
    }

    private function seedUser(string $name, string $email, string $password, bool $admin, int $balance): void
    {
        $user = User::query()->firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make($password), 'is_admin' => $admin, 'admin_role' => $admin ? 'super_admin' : null]);
        if ($admin) $user->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
        $user->wallet()->firstOrCreate([], ['balance' => $balance]);
        if (! $user->fairnessSeeds()->where('active', true)->exists()) app(FairnessSeedService::class)->create($user);
    }
}
