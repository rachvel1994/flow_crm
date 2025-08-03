<?php

namespace App\Exports;

use App\Models\User;
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

class UserExport extends DefaultValueBinder implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithCustomValueBinder,
    ShouldAutoSize
{
    public function collection(): Collection
    {
        return User::with(['phones', 'locations', 'socialLinks', 'user_type'])->get();
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->full_name,
            $user->email,
            $user->user_type?->name ?? '—',
            $user->phones->pluck('number')->join(', '),
            $user->locations->pluck('address')->join(', '),
            $user->socialLinks->pluck('url')->join(', '),
            $user->birthdate?->format('Y-m-d'),
            $user->language,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Full Name',
            'Email',
            'User Type',
            'Phones',
            'Locations',
            'Social Links',
            'Birthdate',
            'Language',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER, // ID
            'E' => NumberFormat::FORMAT_TEXT,   // Phones
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['E'])) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
