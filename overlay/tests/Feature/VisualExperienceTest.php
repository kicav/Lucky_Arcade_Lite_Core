<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_the_visual_shell_and_local_artwork(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-visual-shell', false)
            ->assertSee('/assets/visual/lobby-dice.svg', false)
            ->assertSee('/assets/visual/lobby-roulette.svg', false)
            ->assertSee('/assets/visual/lobby-coin.svg', false)
            ->assertSee('/assets/visual/lobby-slots.svg', false);

        foreach (['brand-mark.svg', 'lobby-dice.svg', 'lobby-roulette.svg', 'lobby-coin.svg', 'lobby-slots.svg'] as $asset) {
            $this->assertFileExists(public_path('assets/visual/'.$asset));
        }
    }

    public function test_each_core_game_exposes_a_visual_animation_target(): void
    {
        $this->seed(DatabaseSeeder::class);
        $player = User::query()->where('email', 'demo@example.com')->firstOrFail();

        foreach (Game::LITE_CODES as $code) {
            $game = Game::query()->where('code', $code)->firstOrFail();
            $this->actingAs($player)
                ->get(route('games.show', $game))
                ->assertOk()
                ->assertSee('data-visual-game="'.$code.'"', false);
        }
    }
}
