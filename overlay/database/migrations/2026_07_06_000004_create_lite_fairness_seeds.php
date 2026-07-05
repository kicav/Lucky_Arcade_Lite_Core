<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fairness_seeds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('server_seed');
            $table->char('server_seed_hash', 64);
            $table->string('client_seed', 64);
            $table->unsignedBigInteger('nonce')->default(0);
            $table->boolean('active')->default(true);
            $table->text('revealed_server_seed')->nullable();
            $table->timestamp('revealed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void { Schema::dropIfExists('fairness_seeds'); }
};
