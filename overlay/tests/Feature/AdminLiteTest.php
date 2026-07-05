<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_only_core_operations(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Operations overview');
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/games')->assertOk();
        $this->actingAs($admin)->get('/admin/entries')->assertOk();
        $this->actingAs($admin)->get('/admin/audit-logs')->assertOk();
        $this->actingAs($admin)->get('/admin/system')->assertOk();
        $this->actingAs($admin)->get('/admin/simulations')->assertNotFound();
        $this->actingAs($admin)->get('/admin/live')->assertNotFound();
    }

    public function test_player_cannot_open_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $player = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $this->actingAs($player)->get('/admin')->assertForbidden();
    }
}
