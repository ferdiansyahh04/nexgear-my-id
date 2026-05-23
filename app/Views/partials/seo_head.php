<?php
/**
 * SEO meta partial — included in <head> by the main layout.
 *
 * Optional variables (any can be omitted):
 *   $seoTitle       (string) — page title; falls back to $title or "NexGear Store"
 *   $seoDescription (string) — meta description
 *   $seoImage       (string) — absolute URL of OG image
 *   $seoType        (string) — Open Graph type, defaults to 'website'
 *   $product        (array)  — when set, JSON-LD Product is emitted
 *   $aggregate      (array)  — review aggregate from ProductController::show()
 */
$seoTitle = $seoTitle ?? ($title ?? 'NexGear Store');
$seoDescription = $seoDescription ?? 'Premium gaming gear — keyboards, mice, headsets, mousepads. Curated for the modern workspace.';
$seoImage = $seoImage ?? 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop';
$seoType = $seoType ?? 'website';
$canonical = current_url();
?>
<meta name="description" content="<?= esc($seoDescription, 'attr') ?>">
<link rel="canonical" href="<?= esc($canonical) ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= esc($seoTitle, 'attr') ?>">
<meta property="og:description" content="<?= esc($seoDescription, 'attr') ?>">
<meta property="og:type" content="<?= esc($seoType, 'attr') ?>">
<meta property="og:url" content="<?= esc($canonical) ?>">
<meta property="og:image" content="<?= esc($seoImage, 'attr') ?>">
<meta property="og:site_name" content="NexGear Store">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($seoTitle, 'attr') ?>">
<meta name="twitter:description" content="<?= esc($seoDescription, 'attr') ?>">
<meta name="twitter:image" content="<?= esc($seoImage, 'attr') ?>">

<!-- Structured data -->
<?php if (! empty($product)):
    $aggregate = $aggregate ?? null;
    $availability = (int) $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
    $jsonLd = [
        '@context'    => 'https://schema.org/',
        '@type'       => 'Product',
        'name'        => $product['name'],
        'description' => $product['description'] ?? '',
        'image'       => $seoImage,
        'sku'         => 'NEX-' . (int) $product['id'],
        'offers'      => [
            '@type'         => 'Offer',
            'priceCurrency' => 'IDR',
            'price'         => (float) $product['price'],
            'availability'  => $availability,
            'url'           => $canonical,
        ],
    ];
    if ($aggregate && (int) ($aggregate['count'] ?? 0) > 0) {
        $jsonLd['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => (float) $aggregate['average'],
            'reviewCount' => (int) $aggregate['count'],
        ];
    }
?>
<script type="application/ld+json" nonce="{csp-script-nonce}"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php else: ?>
<script type="application/ld+json" nonce="{csp-script-nonce}"><?= json_encode([
    '@context' => 'https://schema.org/',
    '@type'    => 'WebSite',
    'name'     => 'NexGear Store',
    'url'      => rtrim(base_url('/'), '/'),
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => rtrim(base_url('/'), '/') . '/products?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>
