<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The plural/display form of a category or subcategory name, exactly as the
 * listings page H1 shows it ("Cake Shop" -> "Cake Shops", "CA" -> "CA's").
 * One place, so the <title> and the H1 always agree.
 */
class LanderNames
{
    /** Service categories whose H1 reads "<name> Services". */
    public const SERVICE_SLUGS = [
        'ac-repair-services', 'electrician', 'plumber', 'pest-control-services',
        'courier-services', 'laptop-repair-services', 'car-repair',
        'cleaning-services', 'home-renovation', 'water-tank-cleaning',
        'refrigerator-repair', 'warehouse-storage-services',
        'cargo-shipping-services', 'logistics', 'transporters',
        'house-keeping-services', 'printing-services',
        'ro-water-purifier-repair-services',
    ];

    /** Last words that name a business, so the page lists many of them. */
    public const BUSINESS_NOUNS = [
        'shop', 'store', 'showroom', 'outlet', 'mart', 'market', 'centre', 'center', 'clinic', 'hospital',
        'lab', 'laboratory', 'pharmacy', 'chemist', 'dealer', 'distributor', 'supplier', 'wholesaler',
        'retailer', 'manufacturer', 'exporter', 'importer', 'trader', 'vendor', 'provider', 'agent',
        'agency', 'broker', 'consultant', 'advisor', 'adviser', 'specialist', 'expert', 'doctor',
        'dentist', 'surgeon', 'therapist', 'physiotherapist', 'dermatologist', 'gynaecologist',
        'gynecologist', 'pediatrician', 'paediatrician', 'nutritionist', 'dietitian', 'psychologist',
        'counsellor', 'counselor', 'lawyer', 'advocate', 'accountant', 'auditor', 'architect',
        'designer', 'decorator', 'planner', 'organiser', 'organizer', 'photographer', 'videographer',
        'artist', 'painter', 'carpenter', 'plumber', 'electrician', 'mechanic', 'technician',
        'contractor', 'builder', 'developer', 'engineer', 'caterer', 'cook', 'chef', 'tutor', 'teacher',
        'trainer', 'coach', 'instructor', 'institute', 'academy', 'school', 'college', 'university',
        'class', 'hotel', 'resort', 'hostel', 'restaurant', 'cafe', 'bakery', 'dhaba', 'bar', 'pub',
        'salon', 'parlour', 'parlor', 'studio', 'gym', 'spa', 'venue', 'hall', 'lawn', 'garden',
        'farmhouse', 'banquet', 'firm', 'company', 'operator', 'driver', 'transporter', 'courier',
        'astrologer', 'reader', 'pandit', 'priest', 'guide', 'band', 'dj', 'tailor', 'boutique',
        'jeweller', 'jeweler', 'optician', 'florist', 'cleaner', 'mover', 'packer', 'repairer',
        'manpower', 'office', 'workshop', 'factory', 'godown', 'warehouse', 'school', 'creche',
        'nursery', 'home', 'apartment', 'flat', 'villa', 'pg', 'stall', 'kiosk', 'service',
    ];

    public static function plural(string $name, ?string $slug = null): string
    {
        $name = trim($name);

        if (in_array($slug, self::SERVICE_SLUGS, true) && !preg_match('/\b(service|services)\s*$/i', $name)) {
            return $name . ' Services';
        }
        if (preg_match('/\b(service|services)\s*$/i', $name)) {
            return $name;
        }
        if (preg_match('/\bnear me$/i', $name) || preg_match('/s$/i', $name) || preg_match('/\sin\s/i', $name)) {
            return $name;
        }
        if (preg_match('/^[A-Z]{2,5}$/', $name)) {
            return $name . "'s";
        }
        if (preg_match('/(^|\s)[A-Z]{2,5}$/', $name)) {
            return $name;
        }

        // Pluralise only a name that ends in a kind of business ("Cake Shop" ->
        // "Cake Shops") and has no joining word. Anything else stays as written:
        // a wrong plural ("Delhis", "Back Pains", "Jewelleries") reads far worse
        // than a singular.
        if (preg_match('/\b(for|to|on|of|near|across|with|from|at|by|under|without|and)\b/i', $name)) {
            return $name;
        }
        $last = mb_strtolower(preg_replace('/[^a-z]/i', '', (string) Str::afterLast($name, ' ')));

        return in_array($last, self::BUSINESS_NOUNS, true) ? Str::plural($name) : $name;
    }
}
