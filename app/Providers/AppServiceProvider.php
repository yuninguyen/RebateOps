<?php

namespace App\Providers;

use App\Models\Account; // Thêm dòng này
use App\Policies\AccountPolicy; // Thêm dòng này
use Illuminate\Support\Facades\Gate; // Thêm dòng này
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['en', 'vi']);
        });

        // 🟢 TIÊU CHUẨN HÓA TIỀN TỆ: Ép hệ thống dùng chuẩn tiếng Anh (USD -> $) bất kể ngôn ngữ giao diện là gì
        \Illuminate\Support\Number::useLocale('en_US');

        // Đăng ký Policy tại đây
        Gate::policy(Account::class, AccountPolicy::class);
        // 🟢 KHÓA CHẶT: Chỉ cho phép Admin xem bất cứ thứ gì liên quan đến Activity Log
        Gate::policy(Activity::class, \App\Policies\ActivityPolicy::class);

        // Đăng ký Observer tại đây
        \App\Models\PayoutLog::observe(\App\Observers\PayoutLogObserver::class);
        \App\Models\RebateTracker::observe(\App\Observers\RebateTrackerObserver::class);
        \App\Models\PayoutMethod::observe(\App\Observers\PayoutMethodObserver::class);
        \App\Models\Email::observe(\App\Observers\EmailObserver::class);

        // 🟢 THÊM NÚT BACK TO TOP: Tự động xuất hiện khi cuộn trang xuống
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render('
                <div id="back-to-top" title="Back to Top">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </div>
                <script>
                    const backToTop = document.getElementById("back-to-top");
                    window.addEventListener("scroll", () => {
                        if (window.pageYOffset > 300) {
                            backToTop.style.display = "flex";
                        } else {
                            backToTop.style.display = "none";
                        }
                    });
                    backToTop.addEventListener("click", () => {
                        window.scrollTo({ top: 0, behavior: "smooth" });
                    });
                </script>
            '),
        );
    }
}
