<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->index();
            $table->string('admin_role', 32)->nullable();
            $table->unsignedBigInteger('daily_stake_limit')->nullable();
            $table->timestamp('self_excluded_until')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason', 255)->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'is_admin', 'admin_role', 'daily_stake_limit', 'self_excluded_until',
                'suspended_at', 'suspension_reason', 'two_factor_secret',
                'two_factor_recovery_codes', 'two_factor_confirmed_at',
            ]);
        });
    }
};
