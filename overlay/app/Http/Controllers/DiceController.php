<?php

namespace App\Http\Controllers;

use App\Actions\Games\PlaceBetAction;
use App\Http\Requests\PlayDiceRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class DiceController extends Controller
{
    public function store(
        PlayDiceRequest $request,
        Game $game,
        PlaceBetAction $action,
    ): RedirectResponse {
        abort_unless($game->code === 'dice', 404);

        $entry = $action->execute(
            user: $request->user(),
            game: $game,
            stake: $request->integer('stake'),
            bet: $request->only(['direction', 'target']),
            requestId: $request->string('request_id')->toString(),
        );

        $message = ($entry->result['won'] ?? false)
            ? "You won {$entry->payout} credits. Roll: {$entry->result['roll']}"
            : "You lost. Roll: {$entry->result['roll']}";

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
