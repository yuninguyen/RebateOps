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
        Schema::table('brands', function (Blueprint $table) {
            // Tỷ giá riêng cho loại Gift Card này (ví dụ: Macy's rate 23.2, Nike rate 23.5)
            $table->decimal('gc_rate', 15, 2)->nullable()->after('maximum_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            //
        });
    }
};
