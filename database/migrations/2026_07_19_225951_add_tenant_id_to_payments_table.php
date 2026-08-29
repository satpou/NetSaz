<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->unique()->after('invoice_id');
            }
            if (!Schema::hasColumn('payments', 'gateway_provider')) {
                $table->string('gateway_provider')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('gateway_provider');
            }
            if (!Schema::hasColumn('payments', 'gateway_reference')) {
                $table->string('gateway_reference')->nullable()->after('gateway_transaction_id');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'proof_of_payment')) {
                $table->string('proof_of_payment')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('payments', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('proof_of_payment')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropColumn('payment_number');
            $table->dropColumn('gateway_provider');
            $table->dropColumn('gateway_transaction_id');
            $table->dropColumn('gateway_reference');
            $table->dropColumn('paid_at');
            $table->dropColumn('proof_of_payment');
            $table->dropForeign(['verified_by']);
            $table->dropColumn('verified_by');
        });
    }
};
