<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->passwordReset()
            ->profile(\App\Filament\Pages\EditProfile::class)
            ->brandName('Penjadwalan Sidang Jurusan Teknik Industri')
            ->favicon(asset('images/logo-website.png'))
            ->globalSearch(false)
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn(): HtmlString => new HtmlString(
                    (request()->route() && request()->route()->getName() === 'filament.admin.auth.login') ? '
                        <div style="background: #f59e0b; padding: 16px 32px; display: flex; align-items: center; gap: 16px; border-bottom: 3px solid #d97706;">
                            <img src="' . asset('images/upn-logo.png') . '" alt="Logo UPN" style="height: 70px; width: auto; object-fit: contain;">
                            <div style="color: #000000ff; font-weight: 800; font-size: 18px; line-height: 1.3; font-family: sans-serif;">
                                UNIVERSITAS PEMBANGUNAN<br>
                                NASIONAL &ldquo;VETERAN&rdquo;<br>
                                YOGYAKARTA
                            </div>
                        </div>
                    ' : ''
                )
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn(): HtmlString => new HtmlString('
                    <div class="topbar-logo-mobile" style="align-items: center; gap: 12px; margin-left: 10px;">
                        <img src="' . asset('images/upn-logo.png') . '" style="height: 32px; width: auto; object-fit: contain;">
                        <div style="line-height: 1.1;">
                            <p style="color: #92400e; font-weight: 800; font-size: 12px; margin: 0; font-family: sans-serif;">TEKNIK INDUSTRI</p>
                            <p style="color: #d97706; font-weight: 600; font-size: 10px; margin: 0; font-family: sans-serif;">UPN &ldquo;VETERAN&rdquo; YOGYAKARTA</p>
                        </div>
                    </div>
                    <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
                        <p class="topbar-title-desktop" style="color: #000000; font-weight: 700; font-size: 18px; margin: 0; font-family: sans-serif; letter-spacing: 0.5px;">Sistem Informasi Penjadwalan Sidang Tugas Akhir</p>
                        <p class="topbar-title-mobile" style="color: #000000; font-weight: 700; font-size: 14px; margin: 0; font-family: sans-serif; line-height: 1.2; text-align: center;">Sistem Penjadwalan Sidang</p>
                    </div>
                ')
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn(): HtmlString => new HtmlString('
                    <div class="sidebar-upn-logo">
                        <img src="' . asset('images/upn-logo.png') . '" style="height: 38px; width: auto; object-fit: contain; flex-shrink: 0;">
                        <div style="line-height: 1.2;">
                            <p style="color: #92400e; font-weight: 800; font-size: 15px; margin: 0 0 4px 0; font-family: sans-serif; white-space: nowrap;">TEKNIK INDUSTRI</p>
                            <p style="color: #d97706; font-weight: 600; font-size: 11px; margin: 0; font-family: sans-serif; white-space: nowrap;">UPN &ldquo;VETERAN&rdquo; YOGYAKARTA</p>
                        </div>
                    </div>
                ')
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn(): HtmlString => new HtmlString('
                    <style>
                        /* ===== RESPONSIVE TOPBAR ELEMENTS ===== */
                        @media (max-width: 1023px) {
                            .topbar-logo-mobile { display: flex !important; }
                            .topbar-title-desktop { display: none !important; }
                            .topbar-title-mobile { display: block !important; }
                        }
                        @media (min-width: 1024px) {
                            .topbar-logo-mobile { display: none !important; }
                            .topbar-title-desktop { display: block !important; }
                            .topbar-title-mobile { display: none !important; }
                        }

                        /* ===== PAGE & LOGIN STYLES ===== */
                        body.fi-body { background: #faf7f2 !important; }
                        .fi-simple-page { background: #ffffff !important; }
                        .fi-simple-main {
                            background: #ffffff !important;
                            border: 1px solid #e5e7eb !important;
                            border-radius: 16px !important;
                            box-shadow: 0 4px 24px rgba(0,0,0,0.06) !important;
                        }

                        /* Branding di Topbar */
                        .fi-logo { display: none !important; } /* Hide default logo */

                        /* MATIKAN TOOLTIP MELAYANG */
                        [role="tooltip"], .tippy-box {
                            display: none !important;
                            visibility: hidden !important;
                            opacity: 0 !important;
                        }

                        /* ===== HANYA BERLAKU DI LAYAR LEBAR (LAPTOP/PC) ===== */
                        @media (min-width: 1024px) {
                            /* 1. Sembunyikan Tombol Toggle (Hapus Panah Total) */
                            .fi-topbar button[data-sidebar-button],
                            .fi-topbar .fi-sidebar-open-button,
                            .fi-topbar .fi-sidebar-close-button,
                            .fi-topbar .fi-topbar-item-button,
                            button[aria-label*="sidebar"],
                            button[aria-controls*="sidebar"] {
                                display: none !important;
                                visibility: hidden !important;
                                width: 0 !important;
                                height: 0 !important;
                                opacity: 0 !important;
                                pointer-events: none !important;
                            }

                            /* ===== CLEAN SIDEBAR HOVER EXPAND (PUSH CONTENT) ===== */
                            .fi-layout { display: flex !important; }

                            .fi-sidebar {
                                width: 5rem !important;
                                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                                overflow-x: hidden !important;
                                z-index: 40 !important;
                                background: #ffffff !important;
                                border-right: 1px solid #e5e7eb !important;
                                position: fixed !important;
                                left: 0 !important;
                                top: 0 !important;
                                height: 100vh !important;
                                bottom: 0 !important;
                                display: flex !important;
                                flex-direction: column !important;
                            }

                            .fi-sidebar:hover {
                                width: 18rem !important;
                                box-shadow: 10px 0 30px rgba(0,0,0,0.05) !important;
                            }

                            .sidebar-upn-logo {
                                position: fixed !important;
                                top: 14px !important;
                                left: 20px !important;
                                display: flex !important;
                                align-items: center !important;
                                gap: 16px !important;
                                z-index: 60 !important;
                                opacity: 0 !important;
                                visibility: hidden !important;
                                pointer-events: none !important;
                                transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            }

                            .fi-sidebar:hover .sidebar-upn-logo {
                                opacity: 1 !important;
                                visibility: visible !important;
                                pointer-events: auto !important;
                            }

                            .fi-main-ctn {
                                margin-left: 5rem !important;
                                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            }

                            .fi-layout:has(.fi-sidebar:hover) .fi-main-ctn {
                                margin-left: 18rem !important;
                            }

                            .fi-sidebar-nav {
                                padding-top: 80px !important;
                                padding-left: 0 !important;
                                padding-right: 0 !important;
                                background: #ffffff !important;
                            }

                            .fi-sidebar-group, .fi-sidebar-nav-groups, .fi-sidebar-group-items {
                                padding: 0 !important;
                                margin: 0 !important;
                            }

                            .fi-sidebar-item {
                                padding: 0 !important;
                                margin: 4px 0 !important;
                                width: 100% !important;
                            }

                            .fi-sidebar-item a {
                                position: relative !important;
                                display: flex !important;
                                align-items: center !important;
                                margin: 4px 12px !important;
                                padding: 12px 0 !important;
                                border-radius: 12px !important;
                                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
                                width: 56px !important;
                                min-height: 48px !important;
                                overflow: hidden !important;
                            }

                            .fi-sidebar:hover .fi-sidebar-item a {
                                width: calc(100% - 24px) !important;
                            }

                            .fi-sidebar-item-icon {
                                position: absolute !important;
                                left: 50% !important;
                                transform: translateX(-50%) !important;
                                width: 1.5rem !important;
                                height: 1.5rem !important;
                                margin: 0 !important;
                                display: block !important;
                                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            }

                            .fi-sidebar:hover .fi-sidebar-item-icon {
                                position: relative !important;
                                left: auto !important;
                                transform: none !important;
                                margin-left: 16px !important;
                                margin-right: 1.2rem !important;
                            }

                            .fi-sidebar-item-label,
                            .fi-sidebar .fi-sidebar-group-label,
                            .fi-sidebar .fi-badge {
                                opacity: 0 !important;
                                visibility: hidden !important;
                                display: none !important;
                            }

                            .fi-sidebar:hover .fi-sidebar-item-label,
                            .fi-sidebar:hover .fi-sidebar-group-label,
                            .fi-sidebar:hover .fi-badge {
                                opacity: 1 !important;
                                visibility: visible !important;
                                display: block !important;
                                width: auto !important;
                                white-space: nowrap !important;
                            }

                            .fi-sidebar-item-active a {
                                background: rgba(245, 158, 11, 0.1) !important;
                            }

                            .fi-sidebar-item-active a .fi-sidebar-item-icon,
                            .fi-sidebar-item-active a .fi-sidebar-item-label {
                                color: #d97706 !important;
                            }

                            .fi-topbar {
                                background: #ffffff !important;
                                border-bottom: 1px solid #e5e7eb !important;
                                left: 0 !important;
                                width: 100% !important;
                                padding-left: 5rem !important;
                                transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            }
                            
                            .fi-layout:has(.fi-sidebar:hover) .fi-topbar {
                                padding-left: 18rem !important;
                            }
                            
                            .fi-topbar nav > div {
                                justify-content: flex-start !important;
                            }
                        }

                        /* UI Details */
                        .fi-main { background-color: #faf7f2 !important; }
                        .fi-ta-ctn { border-radius: 12px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important; }
                    </style>
                ')
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
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
            ]);
    }
}
