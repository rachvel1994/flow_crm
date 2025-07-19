<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Observers\TaskObserver;
use App\Observers\TaskStatusObserver;
use Filament\Resources\Resource;
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
        TaskStatus::observe(TaskStatusObserver::class);
        Task::observe(TaskObserver::class);
        Resource::scopeToTenant(false);
    }
}
