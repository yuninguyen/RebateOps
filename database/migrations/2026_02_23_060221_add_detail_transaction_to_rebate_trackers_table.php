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
        Schema::table('rebate_trackers', function (Blueprint $table) {
            // Sử dụng longText vì dữ liệu Order Details của bạn rất nhiều chữ
            $table->longText('detail_transaction')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rebate_trackers', function (Blueprint $table) {
            $table->dropColumn('detail_transaction');
        });
    }
};
