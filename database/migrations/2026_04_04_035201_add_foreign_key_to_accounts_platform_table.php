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
            // 🟢 TRƯỚC HẾT: Cho phép cột platform nhận giá trị NULL
            // Điều này cần thiết để sử dụng "onDelete('set null')"
            $table->string('platform')->nullable()->change();

            // SAU ĐÓ: Thêm khóa ngoại
            $table->foreign('platform')
                  ->references('slug')
                  ->on('platforms')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['platform']);
        });
    }
};
