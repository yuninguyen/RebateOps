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
            // Kiểm tra và thêm cột 'type' nếu chưa có
            if (!Schema::hasColumn('payout_logs', 'type')) {
                $table->string('type')->default('withdrawal')->after('id');
            }

            // 🟢 THAY ĐỔI TẠI ĐÂY: Nếu 'status' đã có, chúng ta dùng change() để cập nhật thuộc tính
            // Nếu chưa có thì mới add
            if (Schema::hasColumn('payout_logs', 'status')) {
                $table->string('status')->default('hold')->change();
            } else {
                $table->string('status')->default('hold')->after('type');
            }

            // Kiểm tra các cột còn lại
            if (!Schema::hasColumn('payout_logs', 'amount_usd')) {
                $table->decimal('amount_usd', 15, 2)->default(0)->after('status');
            }

            if (!Schema::hasColumn('payout_logs', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 2)->nullable()->after('amount_usd');
            }

            if (!Schema::hasColumn('payout_logs', 'amount_vnd')) {
                $table->decimal('amount_vnd', 15, 2)->nullable()->after('exchange_rate');
            }

            if (!Schema::hasColumn('payout_logs', 'hold_until')) {
                $table->timestamp('hold_until')->nullable()->after('amount_vnd');
            }

            if (!Schema::hasColumn('payout_logs', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('payout_logs')->onDelete('cascade');
            }
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
