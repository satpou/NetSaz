<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('type'); // h-3 | h-1
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('sent'); // sent | failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['invoice_id', 'type']);
            $table->index(['tenant_id', 'status']);
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
