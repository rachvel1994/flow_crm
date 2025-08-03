<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductExport extends DefaultValueBinder implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithCustomValueBinder,
    ShouldAutoSize
{
    public function collection(): Collection
    {
        return Product::with(['category', 'color', 'team'])->get();
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->sku,
            $product->model,
            $product->category?->name,
            $product->price,
            $product->b2b_price,
            $product->sale_price,
            $product->color?->name,
            $product->is_active ? 'Yes' : 'No',
            $product->team?->name,
            implode(', ', $product->images ?? []),
            strip_tags($product->description),
        ];
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'SKU', 'Model', 'Category',
            'Price', 'B2B Price', 'Sale Price', 'Color', 'Active',
            'Team', 'Images', 'Description'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['C'])) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
