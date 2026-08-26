<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = 55;
        $now = now();

        $subcategories = [
            ['name' => 'Mehndi Artist', 'slug' => 'mehndi-artist'],
            ['name' => 'Mehendi Artist Near Me', 'slug' => 'mehendi-artist-near-me'],
            ['name' => 'Mehandi Artist Near Me', 'slug' => 'mehandi-artist-near-me'],
            ['name' => 'Mehndi Near Me', 'slug' => 'mehndi-near-me'],
            ['name' => 'Mehndi Artist Home Service Near Me', 'slug' => 'mehndi-artist-home-service-near-me'],
            ['name' => 'Mehndi Artist Near Me With Price', 'slug' => 'mehndi-artist-near-me-with-price'],
            ['name' => 'Mehendi Near Me', 'slug' => 'mehendi-near-me'],
            ['name' => 'Mehndi Designer Near Me', 'slug' => 'mehndi-designer-near-me'],
            ['name' => 'Mehndi Wala Near Me', 'slug' => 'mehndi-wala-near-me'],
            ['name' => 'Mehndi Services At Home Near Me', 'slug' => 'mehndi-services-at-home-near-me'],
            ['name' => 'Mehndi Artist Near Me With Low Price', 'slug' => 'mehndi-artist-near-me-with-low-price'],
            ['name' => 'Mehendi Artist Near Me With Price', 'slug' => 'mehendi-artist-near-me-with-price'],
            ['name' => 'Mehandi Artist', 'slug' => 'mehandi-artist'],
            ['name' => 'Mehandi Wala Near Me', 'slug' => 'mehandi-wala-near-me'],
            ['name' => 'Bridal Mehendi Artist Near Me', 'slug' => 'bridal-mehendi-artist-near-me'],
            ['name' => 'Mehndi Salon Near Me', 'slug' => 'mehndi-salon-near-me'],
            ['name' => 'Mehendi Artist', 'slug' => 'mehendi-artist'],
            ['name' => 'Mehndi Services Near Me', 'slug' => 'mehndi-services-near-me'],
            ['name' => 'Mehandi Artist Near Me With Price', 'slug' => 'mehandi-artist-near-me-with-price'],
            ['name' => 'Mehendi Wala Near Me', 'slug' => 'mehendi-wala-near-me'],
            ['name' => 'Mehndi Home Service Near Me', 'slug' => 'mehndi-home-service-near-me'],
            ['name' => 'Best Mehndi Artist Near Me', 'slug' => 'best-mehndi-artist-near-me'],
            ['name' => 'Mehendi Designer Near Me', 'slug' => 'mehendi-designer-near-me'],
            ['name' => 'Mehandi Designer Near Me', 'slug' => 'mehandi-designer-near-me'],
            ['name' => 'Mehndi At Home Service', 'slug' => 'mehndi-at-home-service'],
            ['name' => 'Mehendi Service At Home', 'slug' => 'mehendi-service-at-home'],
            ['name' => 'Best Mehendi Artist Near Me', 'slug' => 'best-mehendi-artist-near-me'],
            ['name' => 'Mehndi Near Me With Price', 'slug' => 'mehndi-near-me-with-price'],
            ['name' => 'Near By Mehndi Artist', 'slug' => 'near-by-mehndi-artist'],
            ['name' => 'Mehndi Designs Near Me', 'slug' => 'mehndi-designs-near-me'],
            ['name' => 'Mehndi Home Service', 'slug' => 'mehndi-home-service'],
            ['name' => 'Best Bridal Mehandi Artist', 'slug' => 'best-bridal-mehandi-artist'],
            ['name' => 'Best Designer Mehndi', 'slug' => 'best-designer-mehndi'],
            ['name' => 'Mehndi Artist In Jaipur', 'slug' => 'mehndi-artist-in-jaipur'],
            ['name' => 'Anil Mehandi Art Jaipur', 'slug' => 'anil-mehandi-art-jaipur'],
            ['name' => 'Best Bridal Mehandi Artist In Jaipur', 'slug' => 'best-bridal-mehandi-artist-in-jaipur'],
            ['name' => 'Best Mehandi Artist In Jaipur', 'slug' => 'best-mehandi-artist-in-jaipur'],
            ['name' => 'Best Mehendi Artist In Jaipur', 'slug' => 'best-mehendi-artist-in-jaipur'],
            ['name' => 'Best Mehndi Artist In Jaipur', 'slug' => 'best-mehndi-artist-in-jaipur'],
            ['name' => 'Best Mehndi Designer In Jaipur', 'slug' => 'best-mehndi-designer-in-jaipur'],
            ['name' => 'Best Mehndi Wala In Jaipur', 'slug' => 'best-mehndi-wala-in-jaipur'],
            ['name' => 'Bridal Mehndi Artist In Jaipur', 'slug' => 'bridal-mehndi-artist-in-jaipur'],
            ['name' => 'Bridal Mehndi In Jaipur', 'slug' => 'bridal-mehndi-in-jaipur'],
            ['name' => 'Jaipur Mehandi & Makeup Artist', 'slug' => 'jaipur-mehandi-makeup-artist'],
            ['name' => 'Jaipur Mehandi Art', 'slug' => 'jaipur-mehandi-art'],
            ['name' => 'Jaipur Mehandi Artist', 'slug' => 'jaipur-mehandi-artist'],
            ['name' => 'Jaipur Mehendi Artist', 'slug' => 'jaipur-mehendi-artist'],
            ['name' => 'Jaipur Mehndi Artist', 'slug' => 'jaipur-mehndi-artist'],
            ['name' => 'Jaipur Wali Mehandi', 'slug' => 'jaipur-wali-mehandi'],
            ['name' => 'Mehandi Artist In Jaipur', 'slug' => 'mehandi-artist-in-jaipur'],
            ['name' => 'Mehandi Artist Jaipur', 'slug' => 'mehandi-artist-jaipur'],
            ['name' => 'Mehandi Designer In Jaipur', 'slug' => 'mehandi-designer-in-jaipur'],
            ['name' => 'Mehandi Wala In Jaipur', 'slug' => 'mehandi-wala-in-jaipur'],
            ['name' => 'Mehendi Artist In Jaipur', 'slug' => 'mehendi-artist-in-jaipur'],
            ['name' => 'Mehendi Artist Jaipur', 'slug' => 'mehendi-artist-jaipur'],
            ['name' => 'Mehndi Artist In Jaipur Near Me', 'slug' => 'mehndi-artist-in-jaipur-near-me'],
            ['name' => 'Mehndi Artist In Vaishali Nagar Jaipur', 'slug' => 'mehndi-artist-in-vaishali-nagar-jaipur'],
            ['name' => 'Mehndi Design Jaipur', 'slug' => 'mehndi-design-jaipur'],
            ['name' => 'Mehndi Designer In Jaipur', 'slug' => 'mehndi-designer-in-jaipur'],
            ['name' => 'Mehndi Wala In Jaipur', 'slug' => 'mehndi-wala-in-jaipur'],
            ['name' => 'Mehndi Wali In Jaipur', 'slug' => 'mehndi-wali-in-jaipur'],
            ['name' => 'Top Mehndi Artist In Jaipur', 'slug' => 'top-mehndi-artist-in-jaipur'],
        ];

        foreach ($subcategories as $subcategory) {
            $seo = $this->seoFor($subcategory['name'], $subcategory['slug']);

            $values = [
                'category_id' => $categoryId,
                'name' => $subcategory['name'],
                'seo_title' => $seo['title'],
                'seo_keywords' => $seo['keywords'],
                'seo_description' => $seo['description'],
                'status' => 1,
                'updated_at' => $now,
            ];

            $existing = DB::table('subcategories')
                ->where('category_id', $categoryId)
                ->where('slug', $subcategory['slug'])
                ->first();

            if ($existing) {
                DB::table('subcategories')
                    ->where('id', $existing->id)
                    ->update($values);

                continue;
            }

            DB::table('subcategories')->insert($values + [
                'slug' => $subcategory['slug'],
                'created_at' => $now,
            ]);
        }

        Cache::forget(\App\Models\Subcategory::LOOKUP_CACHE_KEY);
    }

    public function down(): void
    {
        // No-op: this migration may skip rows that already existed before it ran.
    }

    private function seoFor(string $name, string $slug): array
    {
        $isJaipur = str_contains($slug, 'jaipur');
        $isVaishali = str_contains($slug, 'vaishali-nagar');
        $isBridal = str_contains($slug, 'bridal');
        $isPrice = str_contains($slug, 'price');
        $isLowPrice = str_contains($slug, 'low-price');
        $isHome = str_contains($slug, 'home') || str_contains($slug, 'at-home');
        $isDesigner = str_contains($slug, 'designer') || str_contains($slug, 'design');
        $isBest = str_contains($slug, 'best') || str_contains($slug, 'top');
        $isSalon = str_contains($slug, 'salon');
        $isNearMe = str_contains($slug, 'near-me') || str_contains($slug, 'near-by');
        $isWala = str_contains($slug, 'wala') || str_contains($slug, 'wali');
        $isAnil = str_contains($slug, 'anil');
        $isMakeup = str_contains($slug, 'makeup');

        if ($isAnil) {
            return [
                'title' => 'Anil Mehandi Art Jaipur for Bridal and Party Mehndi',
                'keywords' => 'Anil Mehandi Art Jaipur, Jaipur mehandi art, bridal mehndi Jaipur, party mehndi artist Jaipur',
                'description' => 'Connect with Anil Mehandi Art Jaipur for neat bridal, party and festive mehndi work with design options suited for weddings and family functions.',
            ];
        }

        if ($isMakeup) {
            return [
                'title' => 'Jaipur Mehandi and Makeup Artist for Wedding Functions',
                'keywords' => 'Jaipur mehandi makeup artist, bridal mehndi and makeup Jaipur, wedding artist Jaipur, party makeup mehndi',
                'description' => 'Book Jaipur mehandi and makeup support for wedding events where coordinated beauty, henna styling and function-ready service are needed in one place.',
            ];
        }

        if ($isVaishali) {
            return [
                'title' => 'Mehndi Artist in Vaishali Nagar Jaipur for Events',
                'keywords' => 'mehndi artist Vaishali Nagar Jaipur, bridal mehndi Vaishali Nagar, henna artist Vaishali Nagar, party mehndi Jaipur',
                'description' => 'Find mehndi artists in Vaishali Nagar Jaipur for bridal bookings, engagement functions, festivals and home appointments with detailed design choices.',
            ];
        }

        if ($isBridal && $isJaipur) {
            return [
                'title' => $name . ' for Wedding Mehndi Bookings',
                'keywords' => $name . ', bridal mehndi Jaipur, wedding mehndi artist, dulhan mehndi Jaipur, bridal henna designs',
                'description' => $name . ' helps brides compare artists for full-hand, feet and family mehndi sessions across Jaipur wedding functions.',
            ];
        }

        if ($isBridal) {
            return [
                'title' => $name . ' for Bridal Mehndi Designs',
                'keywords' => $name . ', bridal mehndi artist, dulhan mehndi, wedding henna artist, bridal mehendi service',
                'description' => $name . ' is for brides who need detailed henna patterns, function-wise planning and reliable mehndi support before the wedding day.',
            ];
        }

        if ($isLowPrice) {
            return [
                'title' => $name . ' for Budget Friendly Mehndi',
                'keywords' => $name . ', low price mehndi artist, budget mehndi service, affordable mehendi artist, cheap mehndi near me',
                'description' => 'Use ' . $name . ' to discover affordable mehndi options for festivals, small functions and simple party designs without stretching the budget.',
            ];
        }

        if ($isPrice) {
            return [
                'title' => $name . ' with Rates and Booking Options',
                'keywords' => $name . ', mehndi artist price, mehendi charges, bridal mehndi price, henna artist rates',
                'description' => $name . ' helps customers compare mehndi artists by expected charges, design style, event type and booking convenience.',
            ];
        }

        if ($isHome && $isNearMe) {
            return [
                'title' => $name . ' for Doorstep Mehndi Booking',
                'keywords' => $name . ', home mehndi service near me, doorstep mehendi artist, mehndi at home, henna home service',
                'description' => 'Choose ' . $name . ' when you want a mehndi artist to visit your home for bridal trials, family functions, festivals or party designs.',
            ];
        }

        if ($isHome) {
            return [
                'title' => $name . ' for Comfortable At-Home Sessions',
                'keywords' => $name . ', mehndi at home, home mehendi service, doorstep henna artist, party mehndi home service',
                'description' => $name . ' is useful for families who prefer clean, planned and comfortable mehndi appointments at home before special occasions.',
            ];
        }

        if ($isDesigner && $isJaipur) {
            return [
                'title' => $name . ' for Stylish Jaipur Mehndi Patterns',
                'keywords' => $name . ', mehndi designer Jaipur, Arabic mehndi Jaipur, designer henna Jaipur, modern mehndi design',
                'description' => $name . ' brings together Jaipur mehndi designers for Arabic, traditional, modern and custom henna patterns for events.',
            ];
        }

        if ($isDesigner) {
            return [
                'title' => $name . ' for Creative Mehndi Designs',
                'keywords' => $name . ', designer mehndi, Arabic mehndi design, modern mehndi artist, custom henna patterns',
                'description' => 'Explore ' . $name . ' for creative henna work, from minimal patterns to detailed festive, engagement and bridal-inspired designs.',
            ];
        }

        if ($isBest && $isJaipur) {
            return [
                'title' => $name . ' with Trusted Local Artists',
                'keywords' => $name . ', best mehndi artist Jaipur, top mehndi artist Jaipur, Jaipur henna service, wedding mehndi Jaipur',
                'description' => $name . ' highlights Jaipur mehndi professionals for weddings, festive bookings and party functions where finish and timing matter.',
            ];
        }

        if ($isBest) {
            return [
                'title' => $name . ' for Reliable Mehndi Services',
                'keywords' => $name . ', best mehndi artist, top mehendi artist, trusted henna artist, professional mehndi service',
                'description' => 'Search ' . $name . ' to shortlist reliable mehndi professionals for bridal work, family ceremonies, festivals and private parties.',
            ];
        }

        if ($isSalon) {
            return [
                'title' => $name . ' for Salon-Based Henna Services',
                'keywords' => $name . ', mehndi salon, henna salon near me, salon mehendi service, party mehndi salon',
                'description' => $name . ' is suited for customers who prefer salon appointments for clean application, quick designs and planned beauty visits.',
            ];
        }

        if ($isWala && $isJaipur) {
            return [
                'title' => $name . ' for Local Jaipur Mehndi Work',
                'keywords' => $name . ', mehndi wala Jaipur, mehndi wali Jaipur, local henna artist Jaipur, party mehndi Jaipur',
                'description' => $name . ' connects users with local Jaipur mehndi providers for simple designs, festive henna and event-based bookings.',
            ];
        }

        if ($isWala) {
            return [
                'title' => $name . ' for Quick Mehndi Service',
                'keywords' => $name . ', mehndi wala near me, mehendi wala, local henna artist, party mehndi service',
                'description' => 'Find ' . $name . ' for quick henna application, family events, festivals and nearby mehndi help when timing is important.',
            ];
        }

        if ($isJaipur) {
            return [
                'title' => $name . ' for Jaipur Weddings and Festivals',
                'keywords' => $name . ', mehndi artist Jaipur, mehendi artist Jaipur, Jaipur henna artist, wedding mehndi Jaipur',
                'description' => $name . ' helps users find Jaipur-based mehndi artists for wedding events, festive occasions, parties and traditional henna designs.',
            ];
        }

        if ($isNearMe) {
            return [
                'title' => $name . ' for Nearby Mehndi Bookings',
                'keywords' => $name . ', mehndi near me, mehendi artist near me, local henna artist, mehndi service nearby',
                'description' => 'Use ' . $name . ' to locate nearby mehndi artists for bridal functions, festivals, engagements and last-minute henna appointments.',
            ];
        }

        return [
            'title' => $name . ' Services for Events and Functions',
            'keywords' => $name . ', mehndi artist, mehendi service, henna artist, bridal and party mehndi',
            'description' => $name . ' covers professional mehndi service for weddings, festivals, parties and personal occasions with designs matched to the event.',
        ];
    }
};
