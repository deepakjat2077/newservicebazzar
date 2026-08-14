<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportSheet extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithCustomValueBinder
{
    protected Collection $data;
    protected array $headings;
    protected string $title;

    public function __construct(
        Collection $data,
        array $headings,
        string $title
    ) {
        $this->data = $data;
        $this->headings = $headings;
        $this->title = $title;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    /**
     * Prevent CSV/Excel formula injection: any string cell whose value
     * begins with a formula-trigger character (=, +, -, @) is prefixed with
     * a leading apostrophe so spreadsheet applications treat it as literal
     * text instead of executing it as a formula. Exported columns can
     * contain user-submitted data (enquiry name/phone, listing/category
     * names, etc.), so this is applied uniformly rather than per-column.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            $value = "'" . $value;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        // Excel sheet title maximum 31 characters hota hai.
        return mb_substr($this->title, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}