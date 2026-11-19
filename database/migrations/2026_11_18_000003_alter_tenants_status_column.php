<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tenants MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending_payment'");
        } else {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('status', 50)->default('pending_payment')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('trial','active','suspended') NOT NULL DEFAULT 'trial'");
        } else {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('status', 50)->default('trial')->change();
            });
        }
    }
};
