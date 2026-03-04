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
        Schema::table('payout_methods', function (Blueprint $table) {
            // Kiểm tra từng cột, nếu chưa có mới thêm vào
            if (!Schema::hasColumn('payout_methods', 'email')) {
                $table->string('email')->nullable()->after('type');
            }
            if (!Schema::hasColumn('payout_methods', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (!Schema::hasColumn('payout_methods', 'paypal_account')) {
                $table->string('paypal_account')->nullable()->after('password');
            }
            if (!Schema::hasColumn('payout_methods', 'paypal_password')) {
                $table->string('paypal_password')->nullable()->after('paypal_account');
            }
            if (!Schema::hasColumn('payout_methods', 'auth_code')) {
                $table->string('auth_code')->nullable()->after('paypal_password');
            }
            if (!Schema::hasColumn('payout_methods', 'full_name')) {
                $table->string('full_name')->nullable()->after('auth_code');
            }
            if (!Schema::hasColumn('payout_methods', 'dob')) {
                $table->string('dob')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('payout_methods', 'ssn')) {
                $table->string('ssn')->nullable()->after('dob');
            }
            if (!Schema::hasColumn('payout_methods', 'phone')) {
                $table->string('phone')->nullable()->after('ssn');
            }
            if (!Schema::hasColumn('payout_methods', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('payout_methods', 'question_1')) {
                $table->string('question_1')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'answer_1')) {
                $table->string('answer_1')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'question_2')) {
                $table->string('question_2')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'answer_2')) {
                $table->string('answer_2')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'proxy_type')) {
                $table->string('proxy_type')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'ip_address')) {
                $table->string('ip_address')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'isp')) {
                $table->string('isp')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'browser')) {
                $table->string('browser')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'device')) {
                $table->string('device')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'status')) {
                $table->string('status')->default('active')->after('current_balance');
            }
            if (!Schema::hasColumn('payout_methods', 'note')) {
                $table->text('note')->nullable();
            }
            if (!Schema::hasColumn('payout_methods', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            //
        });
    }
};
