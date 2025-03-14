<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'purple' => Color::Purple,
                'snow' => Color::hex('#48CAE4'),
                'gravel' => Color::hex('#B08968'),
                'tarmac' => Color::Slate,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Rally Info')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Results')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Overall Data')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Participants')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('LRČ Website')
            ])
            ->navigationItems([
                NavigationItem::make('Homepage')
                    ->url('https://latvianrally.vercel.app/lv/home', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-home')
                    ->group('LRČ Website'),
                NavigationItem::make('Seasons')
                    ->url('https://latvianrally.vercel.app/lv/seasons', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-calendar-days')
                    ->group('LRČ Website')
            ])
            ->favicon('images/favicon.ico')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ]);
    }
}
