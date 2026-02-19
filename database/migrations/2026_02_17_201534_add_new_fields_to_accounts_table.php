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
        Schema::table('accounts', function (Blueprint $table) {
            // Thêm các cột bạn đang thiếu
            if (!Schema::hasColumn('accounts', 'state')) $table->string('state')->nullable();
            if (!Schema::hasColumn('accounts', 'device')) $table->string('device')->nullable();
            if (!Schema::hasColumn('accounts', 'paypal_info')) $table->text('paypal_info')->nullable();

            // Cột gây lỗi trong thông báo của bạn
            if (!Schema::hasColumn('accounts', 'device_linked_paypal')) {
                $table->string('device_linked_paypal')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
};
