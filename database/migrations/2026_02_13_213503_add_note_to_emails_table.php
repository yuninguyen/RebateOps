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
        Schema::table('emails', function (Blueprint $table) {
            // Thêm cột note sau cột two_factor_code
            $table->text('note')->nullable()->after('two_factor_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            //
        });
    }
};
