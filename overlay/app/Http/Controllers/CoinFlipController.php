<?php

namespace App\Http\Controllers;

use App\Actions\Games\PlaceBetAction;
use App\Http\Requests\PlayCoinFlipRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class CoinFlipController extends Controller
{
    public function store(
        PlayCoinFlipRequest $request,
        Game $game,
        PlaceBetAction $action,
    ): RedirectResponse {
        abort_unless($game->code === 'coinflip', 404);

        $entry = $action->execute(
            user: $request->user(),
            game: $game,
            stake: $request->integer('stake'),
            bet: ['selection' => $request->string('selection')->toString()],
            requestId: $request->string('request_id')->toString(),
        );

        $side = ucfirst((string) $entry->result['side']);
        $message = ($entry->result['won'] ?? false)
            ? "You won {$entry->payout} credits. The coin landed on {$side}."
            : "You lost. The coin landed on {$side}.";

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
