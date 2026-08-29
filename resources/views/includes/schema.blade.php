{{--
    Reusable JSON-LD emitter. Included from includes/seo.blade.php on every
    page; each block only renders when the controller has shared the matching
    variable, so pages that pass nothing emit nothing.

    Expected (all optional) view variables:
      $schemaBreadcrumbs : [ ['name'=>, 'url'=>], ... ]
      $schemaService     : ['name'=>, 'description'=>, 'url'=>, 'areaServed'=>?, 'aggregate'=>['ratingValue'=>,'reviewCount'=>]? ]
      $schemaFaqs        : [ ['question'=>, 'answer'=>], ... ]   (answers must match visible on-page text)
      $schemaListings    : iterable of ['name'=>, 'url'=>, 'address'=>?, 'city'=>?, 'state'=>?, 'ratingValue'=>?, 'ratingCount'=>?]
--}}
@php
    $emit = function (array $data) {
        echo '<script type="application/ld+json">'
            . str_ireplace('</script', '<\/script', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
            . '</script>' . "\n";
    };
@endphp

@if(!empty($schemaBreadcrumbs))
    @php
        $items = [];
        foreach (array_values($schemaBreadcrumbs) as $i => $crumb) {
            if (empty($crumb['name'])) { continue; }
            $item = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $crumb['name'],
            ];
            if (!empty($crumb['url'])) { $item['item'] = $crumb['url']; }
            $items[] = $item;
        }
        if ($items) {
            $emit([
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $items,
            ]);
        }
    @endphp
@endif

@if(!empty($schemaService) && !empty($schemaService['name']))
    @php
        $service = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'serviceType' => $schemaService['name'],
            'name'        => $schemaService['name'],
            'provider'    => [
                '@type' => 'Organization',
                'name'  => $setting->site_name ?? config('app.name'),
                'url'   => url('/'),
            ],
        ];
        if (!empty($schemaService['description'])) { $service['description'] = $schemaService['description']; }
        if (!empty($schemaService['url']))         { $service['url']         = $schemaService['url']; }
        if (!empty($schemaService['areaServed']))  {
            $service['areaServed'] = ['@type' => 'City', 'name' => $schemaService['areaServed']];
        }
        if (!empty($schemaService['aggregate']['reviewCount']) && !empty($schemaService['aggregate']['ratingValue'])) {
            $service['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => round((float) $schemaService['aggregate']['ratingValue'], 1),
                'reviewCount' => (int) $schemaService['aggregate']['reviewCount'],
                'bestRating'  => 5,
                'worstRating' => 1,
            ];
        }
        $emit($service);
    @endphp
@endif

@if(!empty($schemaFaqs))
    @php
        $questions = [];
        foreach ($schemaFaqs as $faq) {
            if (empty($faq['question']) || empty($faq['answer'])) { continue; }
            $questions[] = [
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $faq['answer'],
                ],
            ];
        }
        if ($questions) {
            $emit([
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $questions,
            ]);
        }
    @endphp
@endif

@if(!empty($schemaListings) && (is_array($schemaListings) || $schemaListings instanceof \Countable ? count($schemaListings) : true))
    @php
        $elements = [];
        $position = 0;
        foreach ($schemaListings as $row) {
            $name = is_array($row) ? ($row['name'] ?? null) : ($row->name ?? null);
            $url  = is_array($row) ? ($row['url'] ?? null)  : ($row->url ?? null);
            if (empty($name)) { continue; }
            $position++;
            $business = [
                '@type' => 'LocalBusiness',
                'name'  => $name,
            ];
            if (!empty($url)) { $business['url'] = $url; }

            $address = is_array($row) ? ($row['address'] ?? null) : ($row->address ?? null);
            $city    = is_array($row) ? ($row['city'] ?? null)    : ($row->city ?? null);
            $state   = is_array($row) ? ($row['state'] ?? null)   : ($row->state ?? null);
            if ($address || $city || $state) {
                $postal = ['@type' => 'PostalAddress'];
                if ($address) { $postal['streetAddress']   = $address; }
                if ($city)    { $postal['addressLocality'] = $city; }
                if ($state)   { $postal['addressRegion']   = $state; }
                $postal['addressCountry'] = 'IN';
                $business['address'] = $postal;
            }

            $ratingValue = is_array($row) ? ($row['ratingValue'] ?? null) : ($row->ratingValue ?? null);
            $ratingCount = is_array($row) ? ($row['ratingCount'] ?? null) : ($row->ratingCount ?? null);
            if ($ratingValue && $ratingCount) {
                $business['aggregateRating'] = [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => round((float) $ratingValue, 1),
                    'reviewCount' => (int) $ratingCount,
                    'bestRating'  => 5,
                    'worstRating' => 1,
                ];
            }

            $elements[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'item'     => $business,
            ];
        }
        if ($elements) {
            $emit([
                '@context'        => 'https://schema.org',
                '@type'           => 'ItemList',
                'itemListElement' => $elements,
            ]);
        }
    @endphp
@endif
