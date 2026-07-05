<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FairnessSeed extends Model
{
    protected $fillable = [
        'user_id', 'server_seed', 'server_seed_hash', 'client_seed', 'nonce',
        'active', 'revealed_server_seed', 'revealed_at',
    ];

    protected $hidden = ['server_seed'];

    protected function casts(): array
    {
        return [
            'server_seed' => 'encrypted',
            'nonce' => 'integer',
            'active' => 'boolean',
            'revealed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(GameEntry::class);
    }
}
