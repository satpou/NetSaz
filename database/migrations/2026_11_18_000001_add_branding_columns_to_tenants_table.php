<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('settings');
            }
            if (! Schema::hasColumn('tenants', 'brand_color')) {
                $table->string('brand_color', 20)->nullable()->default('#00C67E')->after('logo_path');
            }
            if (! Schema::hasColumn('tenants', 'brand_updated_at')) {
                $table->timestamp('brand_updated_at')->nullable()->after('brand_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'brand_color', 'brand_updated_at']);
        });
    }
};
