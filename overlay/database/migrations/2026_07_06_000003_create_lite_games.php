<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true)->index();
            $table->unsignedBigInteger('min_bet')->default(10);
            $table->unsignedBigInteger('max_bet')->default(1000);
            $table->json('config')->nullable();
            $table->unsignedBigInteger('active_ruleset_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('games'); }
};
