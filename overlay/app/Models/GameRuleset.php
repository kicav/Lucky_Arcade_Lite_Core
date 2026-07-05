<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRuleset extends Model
{
    protected $fillable = ['game_id', 'engine_version', 'status', 'rules', 'checksum', 'theoretical_rtp_bp', 'activated_at', 'retired_at'];

    protected function casts(): array
    {
        return ['rules' => 'array', 'theoretical_rtp_bp' => 'integer', 'activated_at' => 'datetime', 'retired_at' => 'datetime'];
    }

    public function game(): BelongsTo { return $this->belongsTo(Game::class); }
    public function entries(): HasMany { return $this->hasMany(GameEntry::class, 'game_ruleset_id'); }
    public function isActive(): bool { return $this->status === 'active'; }
}
