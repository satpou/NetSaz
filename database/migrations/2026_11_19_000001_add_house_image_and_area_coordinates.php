<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'house_image')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('house_image')->nullable()->after('ktp_image');
            });
        }

        if (Schema::hasTable('areas')) {
            Schema::table('areas', function (Blueprint $table) {
                if (!Schema::hasColumn('areas', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('description');
                }
                if (!Schema::hasColumn('areas', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'house_image')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('house_image');
            });
        }

        if (Schema::hasTable('areas')) {
            Schema::table('areas', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('areas', 'latitude')) {
                    $columns[] = 'latitude';
                }
                if (Schema::hasColumn('areas', 'longitude')) {
                    $columns[] = 'longitude';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
