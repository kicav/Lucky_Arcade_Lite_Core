<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        return view('dashboard', [
            'wallet' => $user->wallet,
            'entries' => $user->gameEntries()->with('game')->latest()->limit(6)->get(),
            'todayStake' => (int) $user->gameEntries()->whereDate('created_at', today())->sum('stake'),
        ]);
    }
}
