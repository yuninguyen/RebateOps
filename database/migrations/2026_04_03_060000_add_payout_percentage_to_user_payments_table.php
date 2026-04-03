<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_payments', function (Blueprint $blueprint) {
            $blueprint->decimal('payout_percentage', 5, 2)->default(100.00)->after('payout_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_payments', function (Blueprint $blueprint) {
            $blueprint->dropColumn('payout_percentage');
        });
    }
};
