<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('brand')->default('fiberhome'); // fiberhome, vsol, huawei
            $table->string('management_host');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->unsignedSmallInteger('ssh_port')->default(22);
            $table->string('status')->default('offline'); // online, offline
            $table->unsignedInteger('onu_count')->default(0);
            $table->timestamps();
        });

        Schema::create('olt_onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt_devices')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('serial');
            $table->string('pon_id');
            $table->unsignedInteger('onu_id');
            $table->unsignedInteger('vlan')->nullable();
            $table->string('status')->default('provisioning'); // provisioning, active, failed
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_onus');
        Schema::dropIfExists('olt_devices');
    }
};
