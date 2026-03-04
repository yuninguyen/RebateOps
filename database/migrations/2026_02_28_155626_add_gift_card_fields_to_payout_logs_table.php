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
            $table->string('gc_code')->nullable()->after('gc_brand'); // Lưu mã thẻ
            $table->string('gc_pin')->nullable()->after('gc_code');   // Lưu mã PIN
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
            // 🔴 THÊM 2 DÒNG NÀY ĐỂ CÓ THỂ ROLLBACK (HOÀN TÁC):
            $table->dropColumn(['gc_code', 'gc_pin']);
        });
    }
};
