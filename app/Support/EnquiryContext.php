<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Subcategory;

/**
 * What a page sends along with an enquiry that isn't about one business.
 *
 * A listing page carries the business itself, so its enquiries arrive
 * complete. The "Send Enquiry" box on the category, city and search pages
 * carries none, and every one of those forms worked the context out for
 * itself from whatever happened to be in scope — `$selectedCategory` in one,
 * `request('location_id') ?? $location->id` in the next. Half of those
 * variables are never defined on the route that renders the page, so the
 * field went out empty and the lead reached the admin with N/A against it:
 * 106 of 334 enquiries with no business, 58 with no category, 31 with no
 * city, and `cat_loc` (the city page) had no location field at all.
 *
 * The page knows all of this. So the controller that built the page states
 * it once, here, and the form only prints it — nothing is inferred later
 * from what the visitor typed.
 */
class EnquiryContext
{
    /**
     * @param  mixed  $category     Category model, id, or null
     * @param  mixed  $subcategory  Subcategory model, id, or null — its parent
     *                              category is used when no category is given
     * @param  mixed  $location     Location model, id, or name
     * @param  string|null  $searched  What the visitor typed, when the page is
     *                                 a free-text search and nothing matched
     */
    public static function attach(array &$data, $category = null, $subcategory = null, $location = null, ?string $searched = null): void
    {
        $data['enquiryContext'] = self::build($category, $subcategory, $location, $searched);
    }

    public static function build($category = null, $subcategory = null, $location = null, ?string $searched = null): array
    {
        [$categoryId, $categoryName] = self::category($category, $subcategory);
        [$locationId, $locationName] = self::location($location);

        $searched = trim((string) $searched);

        return [
            'category_id'   => $categoryId,
            'category_name' => $categoryName !== '' ? $categoryName : $searched,
            'location_id'   => $locationId,
            'location_name' => $locationName,
            'searched'      => $searched,
        ];
    }

    /** @return array{0: int|null, 1: string} */
    private static function category($category, $subcategory): array
    {
        if (is_object($category) && !empty($category->id)) {
            return [(int) $category->id, (string) ($category->name ?? '')];
        }

        if (is_numeric($category) && (int) $category > 0) {
            $found = Category::find((int) $category);

            return [(int) $category, (string) ($found->name ?? '')];
        }

        // A subcategory page is still a page about its parent category, and
        // `enquiries` only stores a category — so fall back to the parent
        // rather than sending nothing.
        if (is_object($subcategory) && !empty($subcategory->category_id)) {
            $parent = Category::find((int) $subcategory->category_id);

            return [(int) $subcategory->category_id, (string) ($parent->name ?? '')];
        }

        if (is_numeric($subcategory) && (int) $subcategory > 0) {
            $found = Subcategory::find((int) $subcategory);

            if ($found && $found->category_id) {
                $parent = Category::find((int) $found->category_id);

                return [(int) $found->category_id, (string) ($parent->name ?? '')];
            }
        }

        return [null, ''];
    }

    /** @return array{0: int|null, 1: string} */
    private static function location($location): array
    {
        if (is_object($location) && !empty($location->id)) {
            return [(int) $location->id, (string) ($location->name ?? '')];
        }

        // activeLocations() is the cached city list the pages already use, so
        // naming a city costs nothing extra.
        if (is_numeric($location) && (int) $location > 0) {
            $found = activeLocations()->firstWhere('id', (int) $location);

            return [(int) $location, (string) ($found->name ?? '')];
        }

        if (is_string($location) && trim($location) !== '') {
            $name = trim($location);
            $found = activeLocations()->first(
                fn ($city) => strcasecmp((string) $city->name, $name) === 0
            );

            return [$found ? (int) $found->id : null, (string) ($found->name ?? $name)];
        }

        return [null, ''];
    }
}
