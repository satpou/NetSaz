<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('billing_period')->default('monthly'); // monthly, yearly
            $table->unsignedInteger('max_active_customers')->default(0);
            $table->unsignedInteger('max_routers')->default(0);
            $table->unsignedInteger('max_staff')->default(0);
            $table->json('features')->nullable(); // array of feature keys
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
