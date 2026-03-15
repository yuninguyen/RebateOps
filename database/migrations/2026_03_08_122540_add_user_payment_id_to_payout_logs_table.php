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
            // Thêm cột liên kết. Dùng nullOnDelete để nếu xóa phiếu lương thì đơn con vẫn còn (nhưng rỗng ID thanh toán)
            $table->foreignId('user_payment_id')
                ->after('id') // Đặt sau cột id cho dễ nhìn
                ->nullable()
                ->constrained('user_payments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
            $table->dropForeign(['user_payment_id']);
            $table->dropColumn('user_payment_id');
        });
    }
};
