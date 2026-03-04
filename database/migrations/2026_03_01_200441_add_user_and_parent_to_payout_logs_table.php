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
            // 1. Thêm user_id (Gắn nhân viên thực hiện)
            if (!Schema::hasColumn('payout_logs', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id') // Đặt sau cột id cho đẹp cấu trúc
                    ->constrained('users')
                    ->onDelete('set null');
            }

            // 2. Thêm parent_id (Liên kết Rút tiền -> Bán tiền)
            if (!Schema::hasColumn('payout_logs', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('payout_method_id') // Đặt sau ví nhận tiền
                    ->constrained('payout_logs')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
            // Xóa khóa ngoại trước khi xóa cột
            $table->dropForeign(['user_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['user_id', 'parent_id']);
        });
    }
};
