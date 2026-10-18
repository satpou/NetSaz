<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('shortname')->nullable();
            $table->string('host');
            $table->string('status')->default('offline'); // online, offline
            $table->string('connection_type')->default('direct'); // direct, secure_tunnel
            $table->string('api_username')->nullable();
            $table->string('api_password')->nullable();
            $table->unsignedSmallInteger('api_port')->default(8728);
            $table->timestamps();
        });

        // Alias: also expose as 'nas' in some test references
        Schema::create('nas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('shortname')->nullable();
            $table->string('host');
            $table->string('status')->default('offline');
            $table->string('connection_type')->default('direct');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_servers');
        Schema::dropIfExists('nas');
    }
};
