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
        Schema::table('payout_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payout_methods', 'status')) {
                $table->string('status')->default('active')->after('current_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            //
        });
    }
};
