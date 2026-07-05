<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiteCoreSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_lite_core_exposes_only_four_games_and_core_tables(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertOk()->assertSee('Four games');
        $this->assertSame(Game::LITE_CODES, Game::query()->orderBy('id')->pluck('code')->all());
        $this->assertFalse(Schema::hasTable('user_missions'));
        $this->assertFalse(Schema::hasTable('live_events'));
        $this->assertFalse(Schema::hasTable('support_tickets'));
    }

    public function test_player_navigation_is_small_and_core_routes_render(): void
    {
        $this->seed(DatabaseSeeder::class);
        $player = User::query()->where('email', 'demo@example.com')->firstOrFail();

        $this->actingAs($player)->get('/games')->assertOk()->assertSee('Choose your game')->assertDontSee('High Low');
        $this->actingAs($player)->get('/history')->assertOk()->assertSee('Plays and wallet ledger');
        $this->actingAs($player)->get('/fairness')->assertOk()->assertSee('Provably fair seeds');
        $this->actingAs($player)->get('/account')->assertOk()->assertSee('Responsible play');
    }
}
