<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rulesets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('engine_version', 32);
            $table->string('status', 20)->default('active')->index();
            $table->json('rules');
            $table->char('checksum', 64);
            $table->unsignedInteger('theoretical_rtp_bp');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();
            $table->unique(['game_id', 'engine_version']);
            $table->index(['game_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('game_rulesets'); }
};
