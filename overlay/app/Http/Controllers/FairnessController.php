<?php

namespace App\Http\Controllers;

use App\Http\Requests\RotateFairnessSeedRequest;
use App\Models\Game;
use App\Models\GameEntry;
use App\Services\FairnessSeedService;
use App\Services\FairnessVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FairnessController extends Controller
{
    public function show(Request $request, FairnessSeedService $seeds): View
    {
        return view('fairness.show', [
            'activeSeed' => $seeds->active($request->user()),
            'oldSeeds' => $request->user()->fairnessSeeds()
                ->where('active', false)
                ->latest('revealed_at')
                ->limit(20)
                ->get(),
            'verifiableEntries' => $request->user()->gameEntries()
                ->with(['game', 'fairnessSeed'])
                ->whereHas('game', fn ($query) => $query->whereIn('code', Game::LITE_CODES))
                ->whereHas('fairnessSeed', fn ($query) => $query->whereNotNull('revealed_server_seed'))
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function rotate(
        RotateFairnessSeedRequest $request,
        FairnessSeedService $seeds,
    ): RedirectResponse {
        $seeds->rotate($request->user(), $request->input('client_seed'));

        return back()->with('success', 'Seed rotated. The previous server seed is now revealed.');
    }

    public function verify(
        Request $request,
        FairnessVerificationService $verification,
    ): RedirectResponse {
        $data = $request->validate([
            'entry_id' => ['required', 'integer'],
        ]);

        $entry = GameEntry::query()
            ->with(['game', 'fairnessSeed'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($data['entry_id']);

        return back()->with('verification', $verification->verify($entry));
    }
}
