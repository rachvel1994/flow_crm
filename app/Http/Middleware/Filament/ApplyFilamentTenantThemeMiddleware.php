<?php

namespace App\Http\Middleware\Filament;

use Closure;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ApplyFilamentTenantThemeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = filament()->getTenant();

        if (!$tenant) {
            return $next($request);
        }

        // Set the tenant-specific logo
        if ($tenant->getBrandLogo()){
            Filament::getCurrentPanel()->brandLogo($tenant->getBrandLogo());
            Filament::getCurrentPanel()->brandLogoHeight('3.5rem');
        }

        // Set the tenant-specific primary color
        if ($colors = Arr::get($tenant->config, 'colors')) {
            FilamentColor::register([
                'primary' => $tenant->getPrimaryColorCode()
            ]);
        }

        return $next($request);
    }
}
