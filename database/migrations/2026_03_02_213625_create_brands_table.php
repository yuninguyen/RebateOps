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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();

            // 🟢 Tên hiển thị của Brand (Ví dụ: Nike, Amazon)
            $table->string('name');

            // 🟢 Thuộc về Platform nào (Ví dụ: rakuten, joinhoney)
            // Thêm ->index() để việc lọc danh sách ở Select nhanh hơn
            $table->string('platform')->index();

            // 🟢 Khóa định danh duy nhất (Ví dụ: nike, amazon-egift)
            // Dùng để lưu vào cột gc_brand trong bảng payout_logs
            $table->string('slug')->unique();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
