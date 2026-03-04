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
            // 🟢 Xóa hoặc comment dòng current_balance đi vì nó đã tồn tại
            // $table->decimal('current_balance', 15, 2)->default(0)->after('identifier');

            // 🟢 Chỉ giữ lại dòng này
            if (!Schema::hasColumn('payout_methods', 'available_balance')) {
                $table->decimal('available_balance', 15, 2)->default(0)->after('current_balance');
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
