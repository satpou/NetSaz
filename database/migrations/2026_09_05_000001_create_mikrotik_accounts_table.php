<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nas_id')->nullable()->constrained('nas_servers')->nullOnDelete();
            $table->string('pppoe_secret');
            $table->string('pppoe_password');
            $table->string('pppoe_service')->default('pppoe');
            $table->string('status')->default('active'); // active, disabled
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'pppoe_secret']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_accounts');
    }
};
