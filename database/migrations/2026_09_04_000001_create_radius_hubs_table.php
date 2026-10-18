<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radius_hubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('db_name');
            $table->string('server_host');
            $table->unsignedSmallInteger('radius_port')->default(1812);
            $table->string('radius_secret');
            $table->string('status')->default('pending'); // pending, active, inactive
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('radius_hub_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radius_hub_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nas_server_id')->constrained('nas_servers')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius_hub_members');
        Schema::dropIfExists('radius_hubs');
    }
};
