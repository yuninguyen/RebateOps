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
        // Thêm cột status, mặc định là active, đặt sau cột email_password
        $table->string('status')->default('active')->after('email_password');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
Schema::table('emails', function (Blueprint $table) {
        $table->dropColumn('status');
    });
    }
};
