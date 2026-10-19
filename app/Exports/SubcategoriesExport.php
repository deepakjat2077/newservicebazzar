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
    /** $location: name of the Location filter, or null for all locations. */
    /** $dupMap: from SubcategoryDuplicates::map() when the Duplicates filter is on. */
    public function __construct(private Builder $query, private ?string $location = null, private array $dupMap = [])
    {
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Slug', 'Category', 'Location', 'Status', 'SEO Title', 'SEO Keywords', 'SEO Description', 'Page URL', 'Created', 'Duplicate of'];
    }

    public function map($sub): array
    {
        return [
            $sub->id,
            $sub->name,
            $sub->slug,
            optional($sub->category)->name,
            // Subcategories have no city of their own; this is the Location filter
            // the list was exported with (used by listings in that city).
            $this->location ?? 'All locations',
            (int) $sub->status === 1 ? 'Enable' : 'Disable',
            $sub->seo_title,
            $sub->seo_keywords,
            $sub->seo_description,
            $sub->slug ? url('listings/subcategory/' . $sub->slug) : '',
            optional($sub->created_at)->format('Y-m-d'),
            collect($this->dupMap[$sub->id]['others'] ?? [])
                ->map(fn ($o) => '#' . $o['id'] . ' ' . $o['name'] . ($o['category'] ? ' (' . $o['category'] . ')' : ''))
                ->implode('; '),
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 40, 'C' => 40, 'D' => 25, 'E' => 18, 'F' => 10, 'G' => 50, 'H' => 50, 'I' => 60, 'J' => 60, 'K' => 12, 'L' => 60];
    }
}
