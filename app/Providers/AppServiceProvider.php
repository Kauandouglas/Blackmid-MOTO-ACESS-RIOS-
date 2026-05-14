<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        try {
            if (Schema::hasTable('store_settings')) {
                $overrides = StoreSetting::query()->pluck('value', 'key')->all();

                foreach ($overrides as $key => $value) {
                    if ($value !== null && $value !== '') {
                        config([$key => $value]);
                    }
                }

                // Mapear chaves de pagamento do admin para config/services
                $paymentMap = [
                    'payments.mercadopago.access_token' => 'services.mercadopago.access_token',
                    'payments.mercadopago.webhook_secret' => 'services.mercadopago.webhook_secret',
                ];

                foreach ($paymentMap as $dbKey => $configKey) {
                    if (! empty($overrides[$dbKey])) {
                        config([$configKey => $overrides[$dbKey]]);
                    }
                }

            }
        } catch (\Throwable) {
            // Ignora indisponibilidade temporaria de banco para nao quebrar boot.
        }

        if ((bool) env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            try {
                $hasMenus = Schema::hasTable('menus');
                $hasMenuItems = Schema::hasTable('menu_items');
            } catch (\Throwable) {
                $hasMenus = false;
                $hasMenuItems = false;
            }

            if (! $hasMenus || ! $hasMenuItems) {
                $view->with('navigationItems', collect());

                return;
            }

            $menu = Menu::query()
                ->where('active', true)
                ->orderByDesc('id')
                ->first();

            if (! $menu) {
                $view->with('navigationItems', collect());

                return;
            }

            $navigationItems = $menu->items()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with([
                    'category',
                    'children' => function ($query) {
                        $query->where('is_active', true);
                    },
                    'children.category',
                    'children.children' => function ($query) {
                        $query->where('is_active', true);
                    },
                    'children.children.category',
                ])
                ->get();

            $view->with('navigationItems', $navigationItems);
        });
    }
}
