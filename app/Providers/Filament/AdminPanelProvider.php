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
            ->favicon(asset('images/logo-website.png') . '?v=6')
            ->globalSearch(false)
            ->darkMode(false)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): HtmlString => new HtmlString('
                    <link rel="icon" type="image/png" href="' . asset('images/logo-website.png') . '?v=6">
                    <link rel="shortcut icon" type="image/png" href="' . asset('images/logo-website.png') . '?v=6">
                    <link rel="apple-touch-icon" href="' . asset('images/logo-website.png') . '?v=6">
                ')
            )
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn(): HtmlString => new HtmlString(
                    (request()->route() && request()->route()->getName() === 'filament.admin.auth.login') ? '
                        <div style="background: #292C40; padding: 16px 32px; display: flex; align-items: center; gap: 16px; border-bottom: 3px solid #1E2033;">
                            <img src="' . asset('images/logo-ti.png') . '" alt="Logo TI" style="height: 70px; width: auto; object-fit: contain;">
                            <img src="' . asset('images/upn-logo.png') . '" alt="Logo UPN" style="height: 70px; width: auto; object-fit: contain;">
                            <div style="color: #ffffff; font-weight: 800; font-size: 18px; line-height: 1.3; font-family: sans-serif;">
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
                    <div class="topbar-logo-mobile" style="align-items: center; gap: 8px; margin-left: 10px;">
                        <img src="' . asset('images/logo-ti.png') . '" style="height: 32px; width: auto; object-fit: contain;">
                        <img src="' . asset('images/upn-logo.png') . '" style="height: 32px; width: auto; object-fit: contain;">
                        <div style="line-height: 1.1;">
                            <p style="color: #292C40; font-weight: 800; font-size: 12px; margin: 0; font-family: sans-serif;">TEKNIK INDUSTRI</p>
                            <p style="color: #4A5078; font-weight: 600; font-size: 10px; margin: 0; font-family: sans-serif;">UPN &ldquo;VETERAN&rdquo; YOGYAKARTA</p>
                        </div>
                    </div>
                    <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
                        <p class="topbar-title-desktop" style="color: #ffffff; font-weight: 800; font-size: 18px; margin: 0; font-family: sans-serif; letter-spacing: 0.5px;">Sistem Informasi Penjadwalan Sidang Tugas Akhir</p>
                        <p class="topbar-title-mobile" style="color: #ffffff; font-weight: 800; font-size: 14px; margin: 0; font-family: sans-serif; line-height: 1.2; text-align: center;">Sistem Penjadwalan Sidang</p>
                    </div>
                ')
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn(): HtmlString => new HtmlString('
                    <div class="sidebar-upn-logo">
                        <img src="' . asset('images/logo-ti.png') . '" style="height: 38px; width: auto; object-fit: contain; flex-shrink: 0;">
                        <img src="' . asset('images/upn-logo.png') . '" style="height: 38px; width: auto; object-fit: contain; flex-shrink: 0;">
                        <div style="line-height: 1.2; padding-right: 12px; word-break: break-word;">
                            <p style="color: #ffffff; font-weight: 800; font-size: 14px; margin: 0 0 2px 0; font-family: sans-serif; white-space: normal;">TEKNIK INDUSTRI</p>
                            <p style="color: #B1B5CB; font-weight: 700; font-size: 10px; margin: 0; font-family: sans-serif; white-space: normal;">UPN &ldquo;VETERAN&rdquo; YOGYAKARTA</p>
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
                        body.fi-body { background: #ECEDF2 !important; }
                        
                        /* Background image specifically for login/simple pages */
                        body:has(.fi-simple-main) {
                            background-image: url("' . asset('images/foto-jurusan.jpg') . '") !important;
                            background-size: cover !important;
                            background-position: center !important;
                            background-repeat: no-repeat !important;
                            background-attachment: fixed !important;
                            overflow: hidden !important;
                        }
                        
                        /* Overlay gelap untuk background login agar form terbaca */
                        body:has(.fi-simple-main)::before {
                            content: "";
                            position: fixed;
                            top: 0; left: 0; right: 0; bottom: 0;
                            background: rgba(41, 44, 64, 0.6) !important;
                            z-index: -1;
                        }

                        .fi-simple-main {
                            background: #ffffff !important;
                            border: 1px solid #D8DAE5 !important;
                            border-radius: 16px !important;
                            box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important;
                        }

                        /* Branding di Topbar */
                        .fi-logo { display: none !important; } /* Hide default logo */

                        /* ===== TAB STYLES (Filter) ===== */
                        .fi-tabs {
                            background-color: #ECEDF2 !important;
                            border: 1px solid #D8DAE5 !important;
                            border-radius: 12px !important;
                            padding: 6px !important;
                        }

                        .fi-tabs-item {
                            border-radius: 8px !important;
                            font-weight: 600 !important;
                            transition: all 0.2s ease !important;
                            padding: 8px 16px !important;
                        }

                        /* Warna font untuk tab yang tidak aktif */
                        .fi-tabs-item *, .fi-tabs-item span {
                            color: #4A5078 !important;
                        }

                        .fi-tabs-item:hover {
                            background-color: #D8DAE5 !important;
                        }

                        /* Background biru navy untuk tab aktif */
                        .fi-tabs-item[aria-selected="true"],
                        .fi-tabs-item.fi-active,
                        .fi-tabs-item-active {
                            background-color: #292C40 !important;
                            box-shadow: 0 4px 12px rgba(41, 44, 64, 0.2) !important;
                        }

                        /* Warna font putih solid untuk tab aktif */
                        .fi-tabs-item[aria-selected="true"] *,
                        .fi-tabs-item.fi-active *,
                        .fi-tabs-item-active *,
                        .fi-tabs-item[aria-selected="true"] span,
                        .fi-tabs-item.fi-active span {
                            color: #ffffff !important;
                        }

                        /* Warna badge khusus saat aktif */
                        .fi-tabs-item[aria-selected="true"] .fi-badge *,
                        .fi-tabs-item.fi-active .fi-badge *,
                        .fi-tabs-item-active .fi-badge * {
                            color: #292C40 !important;
                        }
                        .fi-tabs-item[aria-selected="true"] .fi-badge,
                        .fi-tabs-item.fi-active .fi-badge,
                        .fi-tabs-item-active .fi-badge {
                            background-color: #ffffff !important;
                        }

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
                                background: #292C40 !important;
                                border-right: 1px solid #1E2033 !important;
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
                                box-shadow: 10px 0 30px rgba(41,44,64,0.08) !important;
                            }

                            .sidebar-upn-logo {
                                position: fixed !important;
                                top: 14px !important;
                                left: 20px !important;
                                display: flex !important;
                                align-items: center !important;
                                gap: 8px !important;
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
                                padding-top: 65px !important;
                                padding-left: 0 !important;
                                padding-right: 0 !important;
                                background: transparent !important;
                                overflow-y: hidden !important;
                                -ms-overflow-style: none;  /* IE and Edge */
                                scrollbar-width: none;  /* Firefox */
                            }
                            .fi-sidebar-nav::-webkit-scrollbar {
                                display: none !important;
                            }

                            .fi-sidebar-group, .fi-sidebar-nav-groups, .fi-sidebar-group-items {
                                padding: 0 !important;
                                margin: 0 !important;
                            }

                            .fi-sidebar-item {
                                padding: 0 !important;
                                margin: 0 !important;
                                width: 100% !important;
                            }

                            .fi-sidebar-item a {
                                position: relative !important;
                                display: flex !important;
                                align-items: center !important;
                                margin: 3px 12px !important;
                                padding: 10px 0 !important;
                                border-radius: 10px !important;
                                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
                                width: 56px !important;
                                min-height: 44px !important;
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
                                color: #D8DAE5 !important;
                            }

                            .fi-sidebar-item-icon {
                                color: #D8DAE5 !important;
                            }

                            .fi-sidebar:hover .fi-sidebar-item-label,
                            .fi-sidebar:hover .fi-sidebar-group-label,
                            .fi-sidebar:hover .fi-badge {
                                opacity: 1 !important;
                                visibility: visible !important;
                                display: block !important;
                                width: auto !important;
                                white-space: nowrap !important;
                                font-weight: 700 !important;
                            }

                            /* Hapus background bawaan (mencegah warna abu-abu terang) */
                            .fi-sidebar-item a,
                            .fi-sidebar-item button {
                                background-color: transparent !important;
                                background: transparent !important;
                            }

                            /* Background saat menu di-hover */
                            .fi-sidebar-item a:hover,
                            .fi-sidebar-item button:hover {
                                background-color: rgba(255, 255, 255, 0.1) !important;
                                background: rgba(255, 255, 255, 0.1) !important;
                            }

                            /* Background saat menu aktif */
                            li.fi-sidebar-item-active > a,
                            li.fi-sidebar-item-active > button,
                            .fi-sidebar-item-active > a {
                                background-color: rgba(255, 255, 255, 0.2) !important;
                                background: rgba(255, 255, 255, 0.2) !important;
                            }

                            /* Pastikan teks & icon selalu putih saat aktif/hover */
                            li.fi-sidebar-item-active > a *,
                            .fi-sidebar-item-active > a *,
                            .fi-sidebar-item a:hover *,
                            .fi-sidebar-item button:hover * {
                                color: #ffffff !important;
                            }

                            .fi-topbar {
                                background: #292C40 !important;
                                border-bottom: 1px solid #1E2033 !important;
                                color: #ffffff !important;
                                left: 0 !important;
                                width: 100% !important;
                                padding-left: 5rem !important;
                                transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            }
                            
                            .fi-topbar button, 
                            .fi-topbar .fi-icon-btn-icon {
                                color: #ffffff !important;
                            }
                            
                            .fi-layout:has(.fi-sidebar:hover) .fi-topbar {
                                padding-left: 18rem !important;
                            }
                            
                            .fi-topbar nav > div {
                                justify-content: flex-start !important;
                            }
                        }

                        /* UI Details */
                        .fi-main { background-color: #ECEDF2 !important; }
                        .fi-ta-ctn { border-radius: 12px !important; box-shadow: 0 1px 4px rgba(41, 44, 64, 0.08) !important; border: 1px solid #D8DAE5 !important; }
                    </style>
                ')
            )
            ->colors([
                'primary' => [
                    50  => '#ECEDF2',
                    100 => '#D8DAE5',
                    200 => '#B1B5CB',
                    300 => '#8A90B1',
                    400 => '#636B97',
                    500 => '#3D4160',
                    600 => '#292C40',
                    700 => '#1E2033',
                    800 => '#141526',
                    900 => '#0A0B19',
                    950 => '#05060D',
                ],
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
