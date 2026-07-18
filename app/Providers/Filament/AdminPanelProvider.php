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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('MULIKU STORE')
            ->sidebarCollapsibleOnDesktop()
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->colors([
                'primary' => Color::Blue,
                'success' => Color::Green,
                'danger' => Color::Red,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn () => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* Gaya khusus halaman Login Putih-Biru Mewah ala Enterprise Retail */
                        body {
                            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 50%, #BFDBFE 100%) !important;
                        }
                        .fi-simple-main {
                            background: #ffffff !important;
                            border-radius: 24px !important;
                            border: 2px solid #E2E8F0 !important;
                            box-shadow: 0 25px 50px -12px rgba(21, 101, 192, 0.18) !important;
                            padding: 36px !important;
                        }
                        .fi-btn-primary {
                            background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%) !important;
                            border: none !important;
                            font-weight: 800 !important;
                            letter-spacing: 0.5px !important;
                            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3) !important;
                            transition: all 0.2s ease !important;
                        }
                        .fi-btn-primary:hover {
                            transform: translateY(-2px) !important;
                            box-shadow: 0 8px 18px rgba(25, 118, 210, 0.4) !important;
                        }
                    </style>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <span style="display: inline-block; padding: 5px 14px; border-radius: 20px; background: #E0F2FE; color: #0369A1; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #BAE6FD;">
                            🏪 GERBANG AKSES STAF & KASIR
                        </span>
                    </div>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\LaporanEksekutif::class,
                \App\Filament\Pages\PesananEksekutif::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn () => \Illuminate\Support\Facades\Blade::render('
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            if (window.innerWidth < 1024) {
                                // Pastikan drawer menu samping tertutup saat halaman pertama dibuka di HP
                                const backdrop = document.querySelector(".fi-sidebar-close-overlay");
                                if (backdrop) backdrop.click();
                            }
                        });
                    </script>
                ')
            )
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
                \App\Http\Middleware\EnsureShiftIsOpen::class,
            ]);
    }
}
