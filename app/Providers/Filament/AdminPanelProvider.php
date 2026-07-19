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
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* =========================================================================
                           GLOBAL MOBILE RESPONSIVE OPTIMIZATION (ANTI GESER-GESER / 0% SIDEWAYS SCROLL)
                           ========================================================================= */
                        @media (max-width: 768px) {
                            /* Matikan scroll horizontal pada kontainer tabel Filament agar langsung pas di layar HP */
                            .fi-ta-content, .fi-ta-ctn, .fi-ta-table-container {
                                overflow-x: visible !important;
                                width: 100% !important;
                            }
                            /* Ubah tabel menjadi kartu vertikal yang rapi di layar HP */
                            .fi-ta-table {
                                display: block !important;
                                width: 100% !important;
                            }
                            .fi-ta-table thead {
                                display: none !important; /* Sembunyikan header tabel, setiap baris jadi kartu mandiri */
                            }
                            .fi-ta-table tbody {
                                display: flex !important;
                                flex-direction: column !important;
                                gap: 14px !important;
                                padding: 8px 4px !important;
                            }
                            .fi-ta-table tbody tr.fi-ta-row {
                                display: flex !important;
                                flex-direction: column !important;
                                background: #ffffff !important;
                                border: 1.5px solid #E2E8F0 !important;
                                border-radius: 18px !important;
                                padding: 14px 16px !important;
                                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
                                gap: 8px !important;
                                position: relative !important;
                                width: 100% !important;
                                box-sizing: border-box !important;
                            }
                            .dark .fi-ta-table tbody tr.fi-ta-row {
                                background: #1E293B !important;
                                border-color: #334155 !important;
                            }
                            .fi-ta-table tbody tr.fi-ta-row td.fi-ta-cell {
                                display: flex !important;
                                align-items: center !important;
                                justify-content: space-between !important;
                                width: 100% !important;
                                padding: 6px 0 !important;
                                border-bottom: 1px dashed #F1F5F9 !important;
                                white-space: normal !important;
                                word-break: break-word !important;
                                box-sizing: border-box !important;
                            }
                            .dark .fi-ta-table tbody tr.fi-ta-row td.fi-ta-cell {
                                border-bottom-color: #334155 !important;
                            }
                            .fi-ta-table tbody tr.fi-ta-row td.fi-ta-cell:last-child {
                                border-bottom: none !important;
                                padding-top: 10px !important;
                                justify-content: flex-end !important;
                            }
                            .fi-ta-actions {
                                display: flex !important;
                                flex-wrap: wrap !important;
                                gap: 8px !important;
                                justify-content: flex-end !important;
                                width: 100% !important;
                            }
                            /* Sembunyikan scrollbar pada bilah filter namun tetap bisa discroll halus */
                            .no-scrollbar::-webkit-scrollbar {
                                display: none !important;
                            }
                            .no-scrollbar {
                                -ms-overflow-style: none !important;
                                scrollbar-width: none !important;
                            }
                        }
                    </style>
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn () => \Illuminate\Support\Facades\Blade::render('
                    @include("filament.components.admin-realtime-notifications")
                    <script>
                        // Pastikan sidebar selalu tertutup saat awal buka di perangkat HP/mobile (< 1024px)
                        function ensureMobileSidebarClosed() {
                            if (window.innerWidth < 1024) {
                                localStorage.setItem("fi_sidebar_is_open", "false");
                                if (window.Alpine && Alpine.store("sidebar")) {
                                    Alpine.store("sidebar").isOpen = false;
                                    if (typeof Alpine.store("sidebar").close === "function") {
                                        Alpine.store("sidebar").close();
                                    }
                                }
                                const closeOverlay = document.querySelector(".fi-sidebar-close-overlay");
                                if (closeOverlay && closeOverlay.offsetParent !== null) {
                                    closeOverlay.click();
                                }
                            }
                        }
                        document.addEventListener("DOMContentLoaded", ensureMobileSidebarClosed);
                        document.addEventListener("alpine:initialized", ensureMobileSidebarClosed);
                        document.addEventListener("livewire:navigated", ensureMobileSidebarClosed);
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
