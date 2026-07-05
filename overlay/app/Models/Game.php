<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    public const LITE_CODES = ['dice', 'roulette', 'coinflip', 'slots'];

    protected $fillable = ['code', 'name', 'description', 'enabled', 'min_bet', 'max_bet', 'config', 'active_ruleset_id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'min_bet' => 'integer', 'max_bet' => 'integer', 'config' => 'array'];
    }

    public function getRouteKeyName(): string { return 'code'; }
    public function activeRuleset(): BelongsTo { return $this->belongsTo(GameRuleset::class, 'active_ruleset_id'); }
    public function rulesets(): HasMany { return $this->hasMany(GameRuleset::class); }
    public function entries(): HasMany { return $this->hasMany(GameEntry::class); }
}
