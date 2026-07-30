<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TOTP (authenticator-app) multi-factor authentication, backed by
 * Filament's first-party Filament\Auth\MultiFactor\App\AppAuthentication
 * provider (see App\Models\User implementing HasAppAuthentication /
 * HasAppAuthenticationRecovery). Both columns are stored via Laravel's
 * `encrypted` / `encrypted:array` casts on the model -- encrypted at rest
 * with APP_KEY, and never logged or serialized (see `$hidden` on User).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('app_authentication_secret')->nullable()->after('password');
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};
