<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        return view('history.index', [
            'entries' => $user->gameEntries()->with('game')->latest()->paginate(20, ['*'], 'plays')->withQueryString(),
            'ledger' => $user->ledgerEntries()->latest()->paginate(20, ['*'], 'ledger')->withQueryString(),
        ]);
    }
}
