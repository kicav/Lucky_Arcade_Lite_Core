<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stake = (int) GameEntry::query()->sum('stake');
        $payout = (int) GameEntry::query()->sum('payout');
        return view('admin.dashboard', [
            'userCount' => User::query()->where('is_admin', false)->count(),
            'gameCount' => Game::query()->whereIn('code', Game::LITE_CODES)->count(),
            'entryCount' => GameEntry::query()->count(),
            'totalStake' => $stake, 'totalPayout' => $payout, 'houseNet' => $stake - $payout,
            'latestEntries' => GameEntry::query()->with(['user', 'game'])->latest()->limit(10)->get(),
        ]);
    }
}
