<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->where('is_admin', false);
        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')->toString()).'%';
            $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }

        return view('admin.users.index', [
            'users' => $query->with('wallet')->withCount('gameEntries')->latest()->paginate(25)->withQueryString(),
        ]);
    }
}
