<?php

namespace App\Providers;

use App\Models\Account; // Thêm dòng này
use App\Policies\AccountPolicy; // Thêm dòng này
use Illuminate\Support\Facades\Gate; // Thêm dòng này
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký Policy tại đây
        Gate::policy(Account::class, AccountPolicy::class);
        // 🟢 KHÓA CHẶT: Chỉ cho phép Admin xem bất cứ thứ gì liên quan đến Activity Log
        Gate::policy(Activity::class, \App\Policies\ActivityPolicy::class);

        // Đăng ký Observer tại đây
        \App\Models\PayoutLog::observe(\App\Observers\PayoutLogObserver::class);
        \App\Models\RebateTracker::observe(\App\Observers\RebateTrackerObserver::class);
        \App\Models\PayoutMethod::observe(\App\Observers\PayoutMethodObserver::class);
        \App\Models\Email::observe(\App\Observers\EmailObserver::class);
    }
}
