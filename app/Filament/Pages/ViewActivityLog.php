<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Noxo\FilamentActivityLog\Pages\ListActivities;

class ViewActivityLog extends ListActivities
{
    use HasPageShield;
    protected static ?string $navigationGroup = 'კონტაქტები';

    protected static ?int $navigationSort = 6;
}
