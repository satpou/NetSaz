<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('portal_login_token', 64)->nullable()->after('portal_pin');
            $table->timestamp('portal_login_token_expires_at')->nullable()->after('portal_login_token');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['portal_login_token', 'portal_login_token_expires_at']);
        });
    }
};
