<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameEntry;
use App\Models\LedgerEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletBetTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_bet_creates_a_settled_entry_and_balanced_ledger(): void
    {
        $this->seed(DatabaseSeeder::class);
        $player = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $game = Game::query()->where('code', 'dice')->firstOrFail();
        $before = $player->wallet->balance;

        $this->actingAs($player)->post(route('games.dice.play', $game), [
            'request_id' => (string) Str::uuid(),
            'stake' => 10,
            'direction' => 'under',
            'target' => 50,
        ])->assertRedirect();

        $entry = GameEntry::query()->sole();
        $this->assertSame('settled', $entry->status);
        $this->assertSame(10, $entry->stake);
        $this->assertNotNull($entry->rules_checksum);
        $this->assertDatabaseHas('ledger_entries', ['type' => 'game_stake', 'amount' => 10]);
        $expected = $before - $entry->stake + $entry->payout;
        $this->assertSame($expected, $player->wallet()->value('balance'));
        $this->assertSame($entry->payout > 0 ? 2 : 1, LedgerEntry::query()->count());
    }

    public function test_request_id_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $player = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $game = Game::query()->where('code', 'coinflip')->firstOrFail();
        $id = (string) Str::uuid();
        $payload = ['request_id' => $id, 'stake' => 10, 'selection' => 'heads'];

        $this->actingAs($player)->post(route('games.coinflip.play', $game), $payload);
        $this->actingAs($player)->post(route('games.coinflip.play', $game), $payload);

        $this->assertSame(1, GameEntry::query()->count());
        $this->assertLessThanOrEqual(2, LedgerEntry::query()->count());
    }
}
