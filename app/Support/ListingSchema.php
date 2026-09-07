<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds the extra LocalBusiness structured-data fields for a listing detail
 * page — geo, opening hours, sameAs, and the specific business @type.
 *
 * Kept out of the Blade template so the parsing (day grouping, 12h->24h time
 * conversion, type mapping) is readable and unit-testable. Everything here is
 * strictly data-gated: a field is emitted only when real, well-formed data
 * exists, because Google penalises structured data that describes something
 * the page can't back up.
 */
class ListingSchema
{
    /**
     * schema.org LocalBusiness subtypes keyed by category slug.
     *
     * Only mappings that are genuinely subtypes of LocalBusiness are listed,
     * so the LocalBusiness property set (address, telephone, geo,
     * openingHoursSpecification, aggregateRating) stays valid on the node.
     * Categories whose closest schema.org type is NOT under LocalBusiness —
     * Hospital, School, College — are deliberately absent and fall back to
     * the generic (valid) 'LocalBusiness'. Anything unmapped does too.
     */
    private const TYPE_BY_CATEGORY_SLUG = [
        'dentists'                 => 'Dentist',
        'dermatologists'           => 'Physician',
        'homeopathic-doctor'       => 'Physician',
        'physiotherapists'         => 'Physician',
        'restaurant'               => 'Restaurant',
        'hotel'                    => 'Hotel',
        'resort'                   => 'Resort',
        'beauty-parlours'          => 'BeautySalon',
        'bridal-makeup-services'   => 'BeautySalon',
        'salon'                    => 'HairSalon',
        'nail-art'                 => 'NailSalon',
        'electrician'              => 'Electrician',
        'plumber'                  => 'Plumber',
        'packers-and-movers'       => 'MovingCompany',
        'lawyers'                  => 'Attorney',
        'chartered-accountant-ca'  => 'AccountingService',
        'travel-agent'             => 'TravelAgency',
        'gym-center'               => 'HealthClub',
        'jewellery-shop'           => 'JewelryStore',
        'pet-shop'                 => 'PetStore',
        'cake-shop'                => 'Bakery',
        'ice-cream'                => 'IceCreamShop',
        'real-estate'              => 'RealEstateAgent',
        'car-repair'               => 'AutoRepair',
        'car-hire'                 => 'AutoRental',
    ];

    private const DAY_MAP = [
        'Mon' => 'Monday',    'Tue' => 'Tuesday',  'Wed' => 'Wednesday',
        'Thu' => 'Thursday',  'Fri' => 'Friday',   'Sat' => 'Saturday',
        'Sun' => 'Sunday',
    ];

    /** Specific LocalBusiness subtype for a category slug, else 'LocalBusiness'. */
    public static function type(?string $categorySlug): string
    {
        return self::TYPE_BY_CATEGORY_SLUG[$categorySlug] ?? 'LocalBusiness';
    }

    /** GeoCoordinates node, or null when lat/lng are missing or non-numeric. */
    public static function geo($listing): ?array
    {
        $lat = trim((string) ($listing->latitude ?? ''));
        $lng = trim((string) ($listing->longitude ?? ''));

        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }

        // Reject obviously bogus coordinates (0,0 and out-of-range).
        $latF = (float) $lat;
        $lngF = (float) $lng;
        if (($latF === 0.0 && $lngF === 0.0) || abs($latF) > 90 || abs($lngF) > 180) {
            return null;
        }

        return [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $latF,
            'longitude' => $lngF,
        ];
    }

    /** Non-empty, absolute social / website URLs for sameAs (deduped). */
    public static function sameAs($listing): array
    {
        $urls = [];

        foreach (['website', 'facebook', 'instagram', 'linkedin', 'twitter', 'youtube'] as $field) {
            $value = trim((string) ($listing->$field ?? ''));
            if ($value !== '' && Str::startsWith($value, ['http://', 'https://'])) {
                $urls[] = $value;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * openingHoursSpecification[] from the parallel business_days / open_time /
     * close_time arrays. Days sharing the same open+close pair are grouped into
     * one entry; a day with a missing or unparseable time is skipped rather
     * than emitted with a broken value.
     */
    public static function openingHours($listing): array
    {
        $days   = self::toArray($listing->business_days ?? null);
        $opens  = self::toArray($listing->open_time ?? null);
        $closes = self::toArray($listing->close_time ?? null);

        if (empty($days)) {
            return [];
        }

        $groups = [];
        foreach ($days as $i => $abbr) {
            $full = self::DAY_MAP[trim((string) $abbr)] ?? null;
            if ($full === null) {
                continue;
            }

            $open  = self::to24($opens[$i] ?? null);
            $close = self::to24($closes[$i] ?? null);
            if ($open === null || $close === null) {
                continue;
            }

            $key = $open . '|' . $close;
            $groups[$key]['opens']    = $open;
            $groups[$key]['closes']   = $close;
            $groups[$key]['days'][]   = $full;
        }

        $out = [];
        foreach ($groups as $g) {
            $out[] = [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => $g['days'],
                'opens'     => $g['opens'],
                'closes'    => $g['closes'],
            ];
        }

        return $out;
    }

    private static function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /** "09:30 AM" -> "09:30", "07:00 PM" -> "19:00"; null if unparseable/empty. */
    private static function to24($time): ?string
    {
        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }

        $ts = strtotime($time);
        if ($ts === false) {
            return null;
        }

        return date('H:i', $ts);
    }
}
