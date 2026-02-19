<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->databaseNotifications()
            ->path('admin')
            ->login()
            ->font('Inter')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Resources\EmailResource\Widgets\EmailStatusChart::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->renderHook(
                'panels::styles.after',
                fn(): string => Blade::render("
                <style>
                    /* Áp dụng cho màn hình máy tính (Desktop) */
                    @media (min-width: 1024px) {
                        /* 1. Thiết lập biến môi trường cho độ rộng sidebar */
                        :root {
                            --sidebar-width: 210px !important;
                        }

                        /* 2. Ép độ rộng cho Aside */
                        .fi-sidebar {
                            width: 205px !important;
                        }

                        /* 3. Đảm bảo nội dung chính tràn hết diện tích còn lại */
                        .fi-main-ctn {
                            width: calc(100% - 195px) !important;
                            flex: 1;
                        }
                        
                        /* 4. Thu gọn padding bên ngoài để menu thoáng hơn */
                        .fi-main {
                            padding-left: 0.1rem !important;
                            padding-right: 0.1rem !important;
                            }

                        /* 4. Thu gọn padding bên trong để menu thoáng hơn */
                        .fi-sidebar-nav {
                            padding-left: 1rem !important;
                            padding-right: 0.5rem !important;
                        }

                        /* 5. Chỉnh font chữ menu nhỏ lại một chút cho chuyên nghiệp */
                        .fi-sidebar-item-label {
                            font-size: 13px !important;
                        }
                     }    
                    
                        span.fi-ta-text-item-label {
                            font-size: 13px !important; 
                            text-align: left !important; /* Căn trái cho nhãn */
                        }

                        .fi-badge {
                            font-size: 11px !important; /* Chỉnh kích thước badge nhỏ hơn */
                            padding: 0.25rem 0.4rem !important; /* Điều chỉnh padding để phù hợp với kích thước mới */  
                        }             
                    
                </style>
            "),
            );
    }

    public function boot(): void
    {
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::body.end',
            fn(): string => "
            <script>
                window.addEventListener('copy-to-clipboard', event => {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(event.detail.text).then(() => {
                            // Thành công
                        });
                    } else {
                        // Phương pháp dự phòng cho trình duyệt cũ
                        const el = document.createElement('textarea');
                        el.value = event.detail.text;
                        document.body.appendChild(el);
                        el.select();
                        document.execCommand('copy');
                        document.body.removeChild(el);
                    }
                });
            </script>
        ",
        );

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::head.end',
            fn(): string => \Illuminate\Support\Facades\Blade::render("
            <style>
                /* Ép độ rộng menu bulk actions để hiện full chữ */
                .fi-dropdown-panel, 
                .fi-ta-bulk-actions-menu-content,
                [class*='fi-dropdown-panel'] {
                    min-width: 100px !important; 
                    max-width: 250px !important; 
                }
                
                /* Đảm bảo nút bấm bên trong cũng dàn đều */
                .fi-dropdown-list-item-label {
                    white-space: nowrap !important;
                }

                /* GIẢI PHÁP CHO TOOLTIP XUỐNG DÒNG */
                .tippy-box, .tippy-content { 
                    white-space: pre-line !important; 
                    word-break: break-word !important;
                    text-align: left !important;
                    max-width: 400px !important; /* Tăng chiều rộng để IP không bị tràn */
                }

                /* CSS CHO NÚT GET ACCOUNT - PHẢI NẰM RIÊNG BIỆT */
                 .get-account-btn {
                    display: block !important; /* Ép xuống dòng */
                    font-weight: 500 !important;
                    color: #d97706 !important; /* Màu xanh */
                    cursor: pointer !important;
                    transition: all 0.2s ease !important;
                }

                .get-account-btn:hover {
                    text-decoration: underline;
                }

            }
            </style>
        "),
        );
    }
}
