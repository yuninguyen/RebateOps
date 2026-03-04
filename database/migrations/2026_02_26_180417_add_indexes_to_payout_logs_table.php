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
        Schema::table('payout_logs', function (Blueprint $table) {
            Schema::table('payout_logs', function (Blueprint $table) {
                $table->index('status');
                $table->index('transaction_type');
                $table->index('created_at');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
            //
        });
    }
};
