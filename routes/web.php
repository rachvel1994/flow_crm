<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/refresh-db', function () {
    Artisan::call('migrate:fresh --seed');
    return 'Database has been refreshed and seeded successfully!';
});
