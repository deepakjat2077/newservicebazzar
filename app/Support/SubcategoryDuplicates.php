<?php

namespace App\Support;

use App\Models\Subcategory;

/**
 * Near-duplicate subcategory names: the same after lower-casing, dropping a
 * leading "1." style number and punctuation, and treating singular/plural as
 * equal ("Accountant" ~ "Accountants", "1. Pet Shops…" ~ "Pet Shops…").
 * Exact duplicates cannot exist (the name is unique), so these are the ones
 * that slip through.
 */
class SubcategoryDuplicates
{
    public static function key(string $name): string
    {
        $x = mb_strtolower($name);
        $x = preg_replace('/^\s*\d+\s*[.)-]\s*/', '', $x);
        $x = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', $x)));

        return implode(' ', array_map(
            fn ($w) => strlen($w) > 3 ? preg_replace('/(es|s)$/', '', $w) : $w,
            explode(' ', $x)
        ));
    }

    /**
     * id => list of [id, name, category] of the OTHER subcategories in its group,
     * for every subcategory that has at least one near-duplicate.
     * $sameCategory: only count matches inside the same category.
     */
    public static function map(bool $sameCategory = true): array
    {
        $subs = Subcategory::with('category:id,name')->get(['id', 'name', 'category_id']);

        $groups = $subs->groupBy(fn ($s) => ($sameCategory ? $s->category_id . '|' : '') . self::key((string) $s->name))
            ->filter(fn ($g) => $g->count() > 1);

        $map = [];
        foreach ($groups as $key => $group) {
            foreach ($group as $s) {
                $map[$s->id] = [
                    'key'    => $key,
                    'others' => $group->where('id', '!=', $s->id)->map(fn ($o) => [
                        'id'       => $o->id,
                        'name'     => $o->name,
                        'category' => optional($o->category)->name,
                    ])->values()->all(),
                ];
            }
        }

        return $map;
    }

    /** IDs ordered so each duplicate group sits together. */
    public static function orderedIds(array $map): array
    {
        uasort($map, fn ($a, $b) => strcmp($a['key'], $b['key']));

        return array_keys($map);
    }
}
