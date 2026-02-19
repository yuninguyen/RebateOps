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
    Schema::create('accounts', function (Blueprint $table) {
        $table->id();
        $table->string('platform');   // Ví dụ: Rakuten, RetailMeNot, PayPal...
        $table->string('username');
        $table->string('password');
        $table->json('status')->default('active'); // active, used, linked, unlink, limited, banned
        $table->text('note')->nullable(); // Ghi chú thêm
        $table->foreignId('user_id')->nullable()->constrained(); // Nhân viên đang giữ
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
}; // Đảm bảo dấu đóng ngoặc này nằm ở cuối file
