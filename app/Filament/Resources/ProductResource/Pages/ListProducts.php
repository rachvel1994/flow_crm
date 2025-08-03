<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
			Action::make('export')
				->label('პროდუქტის ექსპორტი')
				->icon('heroicon-o-arrow-down-tray')
				->action(function () {
					return Excel::download(new ProductExport(), 'products_export_' . now()->format('Ymd_His') . '.xlsx');
				}),
        ];
    }
}
