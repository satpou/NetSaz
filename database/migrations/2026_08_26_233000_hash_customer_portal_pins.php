<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('portal_pin', 255)->nullable()->change();
        });

        $customers = DB::table('customers')->whereNotNull('portal_pin')->get(['id', 'portal_pin']);

        foreach ($customers as $customer) {
            $pin = $customer->portal_pin;
            if (is_string($pin) && strlen($pin) === 6 && ctype_digit($pin)) {
                DB::table('customers')->where('id', $customer->id)->update([
                    'portal_pin' => Hash::make($pin),
                ]);
            }
        }

        // Invalidate legacy plaintext magic tokens (now stored as sha256 hashes)
        DB::table('customers')->whereNotNull('portal_login_token')->update([
            'portal_login_token' => null,
            'portal_login_token_expires_at' => null,
        ]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('portal_pin', 6)->nullable()->change();
        });
    }
};
