<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Maintenance;
use App\Models\SparePart;
use App\Models\User;
use App\Observers\MaintenanceObserver;
use Filament\Notifications\Notification; // Pastikan ini dari Filament

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Fix asset URL if using artisan serve or non-standard port
        if ($this->app->environment('local')) {
            $this->app['url']->forceRootUrl(config('app.url'));
        }
        
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        // Bagikan data maintenance ke semua view (lazy loading dengan closure)
        View::share('maintenances', fn() => Maintenance::latest()->get());

        // Komposer view untuk notifikasi & root HTML tag
        View::composer('*', function ($view) {
            $view->with([
                'unreadNotifications' => Auth::check() ? Auth::user()->unreadNotifications : collect(),
                'rootHtmlTag' => '<div></div>',
            ]);
        });

        // Daftarkan observer untuk Maintenance
        Maintenance::observe(MaintenanceObserver::class);

        // Notifikasi stok menipis pada SparePart
        SparePart::updated(function ($sparePart) {
            if ($sparePart->stock < $sparePart->min_stock) {
                Notification::make()
                    ->title('Stok Menipis: ' . $sparePart->name)
                    ->sendToDatabase(User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get());
            }
        });
    }
}
