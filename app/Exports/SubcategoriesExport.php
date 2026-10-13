<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Admin > Sub Category > Export. Takes the same filtered query the list page
 * uses, so the file holds exactly what the filters show (all pages, not just 20).
 */
class SubcategoriesExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths
{
    public function __construct(private Builder $query)
    {
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Slug', 'Category', 'Status', 'SEO Title', 'SEO Keywords', 'SEO Description', 'Page URL', 'Created'];
    }

    public function map($sub): array
    {
        return [
            $sub->id,
            $sub->name,
            $sub->slug,
            optional($sub->category)->name,
            (int) $sub->status === 1 ? 'Enable' : 'Disable',
            $sub->seo_title,
            $sub->seo_keywords,
            $sub->seo_description,
            $sub->slug ? url('listings/subcategory/' . $sub->slug) : '',
            optional($sub->created_at)->format('Y-m-d'),
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 40, 'C' => 40, 'D' => 25, 'E' => 10, 'F' => 50, 'G' => 50, 'H' => 60, 'I' => 60, 'J' => 12];
    }
}
