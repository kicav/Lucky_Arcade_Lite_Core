<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EntryController as AdminEntryController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\SystemController as AdminSystemController;
use App\Http\Controllers\Admin\UserActionController as AdminUserActionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\CoinFlipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiceController;
use App\Http\Controllers\FairnessController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RouletteController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SlotsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:8,1')->name('login.store');
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:8,1')->name('two-factor.challenge.store');
    Route::post('/two-factor-challenge/cancel', [TwoFactorChallengeController::class, 'cancel'])->name('two-factor.challenge.cancel');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
    Route::post('/security/two-factor/begin', [SecurityController::class, 'begin'])->middleware('throttle:5,1')->name('security.two-factor.begin');
    Route::post('/security/two-factor/confirm', [SecurityController::class, 'confirm'])->middleware('throttle:8,1')->name('security.two-factor.confirm');
    Route::delete('/security/two-factor', [SecurityController::class, 'disable'])->middleware('throttle:5,1')->name('security.two-factor.disable');
    Route::post('/security/recovery-codes', [SecurityController::class, 'regenerateRecoveryCodes'])->middleware('throttle:5,1')->name('security.recovery-codes.regenerate');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/history', HistoryController::class)->name('history');
    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->middleware('throttle:5,1')->name('account.password.update');
    Route::put('/account/play-controls', [AccountController::class, 'updatePlayControls'])->name('account.controls.update');

    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games/{game}/dice', [DiceController::class, 'store'])->middleware('throttle:30,1')->name('games.dice.play');
    Route::post('/games/{game}/roulette', [RouletteController::class, 'store'])->middleware('throttle:30,1')->name('games.roulette.play');
    Route::post('/games/{game}/coinflip', [CoinFlipController::class, 'store'])->middleware('throttle:30,1')->name('games.coinflip.play');
    Route::post('/games/{game}/slots', [SlotsController::class, 'store'])->middleware('throttle:30,1')->name('games.slots.play');

    Route::get('/fairness', [FairnessController::class, 'show'])->name('fairness.show');
    Route::post('/fairness/rotate', [FairnessController::class, 'rotate'])->middleware('throttle:5,1')->name('fairness.rotate');
    Route::post('/fairness/verify', [FairnessController::class, 'verify'])->middleware('throttle:20,1')->name('fairness.verify');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->middleware('admin.area:dashboard')->name('dashboard');
    Route::get('/games', [AdminGameController::class, 'index'])->middleware('admin.area:games')->name('games.index');
    Route::put('/games/{game}', [AdminGameController::class, 'update'])->middleware('admin.area:games')->name('games.update');
    Route::get('/users', [AdminUserController::class, 'index'])->middleware('admin.area:users')->name('users.index');
    Route::get('/users/{user}', [AdminUserActionController::class, 'show'])->middleware('admin.area:users')->name('users.show');
    Route::post('/users/{user}/suspend', [AdminUserActionController::class, 'suspend'])->middleware('admin.area:user_actions')->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [AdminUserActionController::class, 'unsuspend'])->middleware('admin.area:user_actions')->name('users.unsuspend');
    Route::post('/users/{user}/grant', [AdminUserActionController::class, 'grant'])->middleware(['admin.area:user_actions', 'throttle:10,1'])->name('users.grant');
    Route::get('/entries', [AdminEntryController::class, 'index'])->middleware('admin.area:entries')->name('entries.index');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->middleware('admin.area:audit')->name('audit.index');
    Route::get('/system', [AdminSystemController::class, 'index'])->middleware('admin.area:system')->name('system.index');
    Route::post('/system/backup', [AdminSystemController::class, 'backup'])->middleware(['admin.area:system', 'throttle:2,1'])->name('system.backup');
    Route::post('/system/reconcile', [AdminSystemController::class, 'reconcile'])->middleware(['admin.area:system', 'throttle:2,1'])->name('system.reconcile');
});
