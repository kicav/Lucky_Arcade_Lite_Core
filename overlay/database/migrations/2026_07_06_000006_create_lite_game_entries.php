<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->restrictOnDelete();
            $table->foreignId('game_ruleset_id')->nullable()->constrained('game_rulesets')->nullOnDelete();
            $table->foreignId('fairness_seed_id')->constrained()->restrictOnDelete();
            $table->string('engine_version', 32)->nullable();
            $table->json('rules_snapshot')->nullable();
            $table->char('rules_checksum', 64)->nullable();
            $table->unsignedBigInteger('stake');
            $table->unsignedBigInteger('payout')->default(0);
            $table->bigInteger('net');
            $table->json('bet');
            $table->json('result');
            $table->string('client_seed', 64);
            $table->unsignedBigInteger('nonce');
            $table->char('server_seed_hash', 64);
            $table->uuid('request_id');
            $table->string('status', 20)->default('settled');
            $table->timestamps();
            $table->unique(['user_id', 'request_id']);
            $table->index(['game_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('game_entries'); }
};
