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
        Schema::table('user_payments', function (Blueprint $table) {
            // Tỷ giá thực tế trả cho User (User Rate)
            // Cột exchange_rate cũ sẽ dùng làm Market Rate (Tỷ giá gốc)
            $table->decimal('payout_rate', 15, 2)->after('exchange_rate')->nullable();
            
            // Số tiền lãi (Profit) tính theo VND
            $table->decimal('profit_vnd', 20, 2)->after('total_vnd')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->dropColumn(['payout_rate', 'profit_vnd']);
        });
    }
};
