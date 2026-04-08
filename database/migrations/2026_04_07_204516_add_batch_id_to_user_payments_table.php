<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('status')->index(); // Nhóm thanh toán (lô)
            $table->string('asset_group')->nullable()->after('batch_id'); // Loại tài sản (gift_card, paypal)
            $table->dateTime('payment_date')->nullable()->after('asset_group'); // Ngày thanh toán thực tế (Quyết toán)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->dropColumn(['batch_id', 'asset_group', 'payment_date']);
        });
    }
};
