<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payout_methods', 'deleted_at')) {
            Schema::table('payout_methods', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('brands', 'deleted_at')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
