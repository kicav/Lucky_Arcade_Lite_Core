<?php

namespace App\Http\Controllers;

use App\Actions\Games\PlaceBetAction;
use App\Http\Requests\PlayRouletteRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class RouletteController extends Controller
{
    public function store(
        PlayRouletteRequest $request,
        Game $game,
        PlaceBetAction $action,
    ): RedirectResponse {
        abort_unless($game->code === 'roulette', 404);

        $entry = $action->execute(
            user: $request->user(),
            game: $game,
            stake: $request->integer('stake'),
            bet: [
                'type' => $request->string('bet_type')->toString(),
                'selection' => $request->string('selection')->toString(),
            ],
            requestId: $request->string('request_id')->toString(),
        );

        $message = ($entry->result['won'] ?? false)
            ? "You won {$entry->payout} credits. Number: {$entry->result['number']} ({$entry->result['color']})"
            : "You lost. Number: {$entry->result['number']} ({$entry->result['color']})";

        return back()->with([
            'result' => $message,
            'game_result' => [
                'game' => $game->code,
                'won' => (bool) ($entry->result['won'] ?? false),
                'payout' => $entry->payout,
                'result' => $entry->result,
            ],
        ]);
    }
}
