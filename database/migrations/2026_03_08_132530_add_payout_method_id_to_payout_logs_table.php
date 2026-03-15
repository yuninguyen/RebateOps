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
        // Kiểm tra: Nếu CHƯA CÓ cột payout_method_id thì mới tạo
        if (!Schema::hasColumn('payout_logs', 'payout_method_id')) {
            Schema::table('payout_logs', function (Blueprint $table) {
                $table->foreignId('payout_method_id')->nullable()->constrained('payout_methods')->nullOnDelete();
            });
        }
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
