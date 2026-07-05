<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('admin.games.index', ['games' => Game::query()->whereIn('code', Game::LITE_CODES)->orderBy('id')->get()]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        abort_unless(in_array($game->code, Game::LITE_CODES, true), 404);
        $data = $request->validate(['enabled' => ['nullable', 'boolean'], 'min_bet' => ['required', 'integer', 'min:1'], 'max_bet' => ['required', 'integer', 'gte:min_bet', 'max:1000000']]);
        $before = $game->only(['enabled', 'min_bet', 'max_bet']);
        $game->update(['enabled' => $request->boolean('enabled'), 'min_bet' => $data['min_bet'], 'max_bet' => $data['max_bet']]);
        AuditLog::query()->create(['actor_id' => $request->user()->id, 'action' => 'game.settings.updated', 'subject_type' => Game::class, 'subject_id' => $game->id, 'before' => $before, 'after' => $game->only(['enabled', 'min_bet', 'max_bet']), 'ip_address' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000), 'created_at' => now()]);
        return back()->with('success', 'Game settings updated.');
    }
}
