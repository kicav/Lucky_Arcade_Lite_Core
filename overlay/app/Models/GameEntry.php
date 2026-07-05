<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameEntry extends Model
{
    protected $fillable = [
        'user_id', 'game_id', 'fairness_seed_id', 'stake', 'payout', 'net',
        'bet', 'result', 'client_seed', 'nonce', 'server_seed_hash',
        'request_id', 'status', 'game_ruleset_id', 'engine_version', 'rules_snapshot', 'rules_checksum',
    ];

    protected function casts(): array
    {
        return [
            'stake' => 'integer',
            'payout' => 'integer',
            'net' => 'integer',
            'bet' => 'array',
            'result' => 'array',
            'nonce' => 'integer',
            'rules_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(GameRuleset::class, 'game_ruleset_id');
    }

    public function fairnessSeed(): BelongsTo
    {
        return $this->belongsTo(FairnessSeed::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }
}
