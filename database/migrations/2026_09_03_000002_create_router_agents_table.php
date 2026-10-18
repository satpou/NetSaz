<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('router_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nas_id')->constrained('nas')->cascadeOnDelete();
            $table->string('agent_token'); // stored as hash
            $table->string('status')->default('offline'); // online, offline
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('router_agent_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_agent_id')->constrained()->cascadeOnDelete();
            $table->string('command_type');
            $table->json('payload')->nullable();
            $table->string('status')->default('pending'); // pending, acknowledged, failed
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_agent_commands');
        Schema::dropIfExists('router_agents');
    }
};
