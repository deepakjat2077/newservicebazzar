<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** Admin > Listing > "Small images" Excel. Rows come from ListingImageSizes::report(). */
class SmallListingImagesExport implements FromCollection, WithHeadings, WithColumnWidths
{
    public function __construct(private Collection $rows, private int $minSide)
    {
    }

    public function collection()
    {
        return $this->rows->map(fn ($r) => [
            $r['id'], $r['name'], $r['category'], $r['city'], $r['status'],
            $r['total'], $r['small'], $r['small_thumb'], $r['smallest'], $r['missing'],
            $r['edit_url'], $r['page_url'],
        ]);
    }

    public function headings(): array
    {
        return [
            'ID', 'Listing name', 'Category', 'City', 'Status',
            'Total images', "Small images (< {$this->minSide} px)", 'Main photo small?', 'Smallest image (px)', 'Missing files',
            'Admin edit link', 'Page link',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 40, 'C' => 24, 'D' => 16, 'E' => 11, 'F' => 13, 'G' => 22, 'H' => 18, 'I' => 20, 'J' => 13, 'K' => 50, 'L' => 60];
    }
}
