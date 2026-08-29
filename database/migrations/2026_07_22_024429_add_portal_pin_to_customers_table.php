<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('portal_pin', 6)->nullable()->after('join_date');
            $table->timestamp('portal_last_login_at')->nullable()->after('portal_pin');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['portal_pin', 'portal_last_login_at']);
        });
    }
};
