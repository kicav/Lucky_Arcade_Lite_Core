<?php

namespace App\Http\Controllers;

use App\Actions\Games\PlaceBetAction;
use App\Http\Requests\PlaySlotsRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class SlotsController extends Controller
{
    public function store(
        PlaySlotsRequest $request,
        Game $game,
        PlaceBetAction $action,
    ): RedirectResponse {
        abort_unless($game->code === 'slots', 404);

        $entry = $action->execute(
            user: $request->user(),
            game: $game,
            stake: $request->integer('stake'),
            bet: ['lines' => 1],
            requestId: $request->string('request_id')->toString(),
        );

        $symbols = implode(' · ', array_map('ucfirst', $entry->result['symbols']));
        $message = ($entry->result['won'] ?? false)
            ? "You won {$entry->payout} credits. {$symbols}."
            : "No match this spin. {$symbols}.";

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
