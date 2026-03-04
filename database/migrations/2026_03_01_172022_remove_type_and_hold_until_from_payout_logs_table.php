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
            // Xóa mảng các cột không cần thiết
            $table->dropColumn(['type', 'hold_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
            // Nếu rollback thì thêm lại (có thể để trống nếu không quan trọng)
            $table->string('type')->nullable();
            $table->timestamp('hold_until')->nullable();
        });
    }
};
