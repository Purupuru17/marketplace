<?php

namespace IdCore\CoreStarter\Providers;

use IdCore\CoreStarter\Support\MenuTreeBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config package dengan config aplikasi utama
        $this->mergeConfigFrom(
            __DIR__.'/../../config/idcore.php',
            'idcore'
        );
    }

    public function boot(): void
    {
        // 1. Daftarkan route package
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // 2. Daftarkan view dengan namespace 'idcore::'
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'idcore');

        // 3. Daftarkan migration package (auto ke-load saat php artisan migrate)
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // 4. Integrasi Blade Components secara Otomatis
        View::composer('idcore::layouts.partials.sidebar', function ($view) {
            $view->with('menuTree', MenuTreeBuilder::forUser(Auth::user()));
        });

        // 5. Izinkan publish config ke proyek utama (opsional tapi best practice)
        $this->publishes([__DIR__.'/../../config/idcore.php' => config_path('idcore.php')], 'idcore-config');

        // 6. Component Blade otomatis, sehingga bisa dipakai di view tanpa perlu register manual
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/components', 'idcore');

    }
}
