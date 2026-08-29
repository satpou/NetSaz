<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // Populate tenant_id untuk data existing (untuk super_admin, set null)
        // Di production, Anda perlu assign ke tenant tertentu

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_email_unique');
            $table->unique(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->dropColumn('tenant_id');
        });
    }
};
