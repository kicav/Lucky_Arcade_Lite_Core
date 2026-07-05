<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FairnessSeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View { return view('auth.register'); }

    public function store(Request $request, FairnessSeedService $seeds): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email', '')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data, $seeds): User {
            $user = User::query()->create($data);
            $user->wallet()->create(['balance' => 10000]);
            $seeds->create($user);
            return $user;
        }, attempts: 3);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('games.index');
    }
}
