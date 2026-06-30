<?php
/**
 * M2 Bygg Team AB — Global Header
 * Design: Premium dark nav matching reference design.
 */
$site_name   = 'M2 Bygg Team AB';
$base_url    = 'https://www.m2team.se';
$phone1      = '073 240 50 26';
$phone1_raw  = '031968888';
$phone2      = '0732-40 50 26';
$phone2_raw  = '0732405026';
$email_addr  = 'info@m2team.se';
$address     = 'Lillhagsvägen 88, 442 43 Hisings Backa';
$instagram   = 'https://www.instagram.com/m2byggteam/';
$facebook    = 'https://www.facebook.com/profile.php?id=61577099783558';

$page_title       = $page_title       ?? $site_name;
$page_description = $page_description ?? 'M2 Bygg Team AB – Professionella tak- och fasadarbeten i Göteborg. Fast pris och ROT-avdrag.';
$page_canonical   = $page_canonical   ?? $base_url . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active_page      = $active_page      ?? '';
$og_image         = $og_image         ?? $base_url . '/assets/images/hero-house.webp';
$full_title       = ($page_title === $site_name) ? $site_name : $page_title . ' | ' . $site_name;
$page_lang        = function_exists('current_lang') ? current_lang() : 'sv';
$breadcrumbs      = $breadcrumbs ?? null;

$nav_service_groups = [
  'Takläggning' => [
    ['Betongtak', '/taklaggning/betongtak'],
    ['Plåttak',   '/taklaggning/plattak'],
    ['Papptak',   '/taklaggning/papptak'],
  ],
  'Målning' => [
    ['Fasadmålning',    '/malning/fasadmalning'],
    ['Takmålning',      '/malning/takmalning'],
    ['Inomhusmålning',  '/malning/inomhusmalning'],
    ['Övrig målning',   '/malning/ovrig-malning'],
  ],
  'Renovering & tvätt' => [
    ['Renovering',      '/renovering'],
    ['Taktvätt',        '/tak-fasadtvatt/taktvatt'],
    ['Fasadtvätt',       '/tak-fasadtvatt/fasadtvatt'],
  ],
];
// Flat list — used by the mobile menu, which stays single-column.
$nav_services = array_merge(...array_values($nav_service_groups));

if (!function_exists('e')) {
    function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

// Real review data for the LocalBusiness schema below — computed from actual customer
// reviews rather than a hardcoded placeholder, so search engines see accurate ratings.
if (!function_exists('db')) require_once __DIR__ . '/../crm/includes/db.php';
$schemaReviewCount = (int)db()->query("SELECT COUNT(*) FROM reviews WHERE visible = 1")->fetchColumn();
$schemaAvgRating   = $schemaReviewCount > 0 ? (float)db()->query("SELECT AVG(rating) FROM reviews WHERE visible = 1")->fetchColumn() : 0;
$schemaTopReviews  = $schemaReviewCount > 0 ? db()->query(
    "SELECT r.rating, r.body, c.name AS customer_name FROM reviews r JOIN customers c ON c.id = r.customer_id
     WHERE r.visible = 1 ORDER BY r.created_at DESC LIMIT 5"
)->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="<?= e($page_lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1D1D1F">
  <title><?= e($full_title) ?></title>
  <meta name="description" content="<?= e($page_description) ?>">
  <link rel="canonical" href="<?= e($page_canonical) ?>">

  <!-- Google Search Console / Bing Webmaster Tools verification — fill in real codes when available -->
  <?php if (defined('GSC_VERIFICATION_CODE')): ?><meta name="google-site-verification" content="<?= e(GSC_VERIFICATION_CODE) ?>"><?php endif; ?>
  <?php if (defined('BING_VERIFICATION_CODE')): ?><meta name="msvalidate.01" content="<?= e(BING_VERIFICATION_CODE) ?>"><?php endif; ?>

  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= e($page_canonical) ?>">
  <meta property="og:title"       content="<?= e($full_title) ?>">
  <meta property="og:description" content="<?= e($page_description) ?>">
  <meta property="og:image"       content="<?= e($og_image) ?>">
  <meta property="og:locale"      content="<?= $page_lang === 'en' ? 'en_US' : 'sv_SE' ?>">
  <meta property="og:site_name"   content="<?= e($site_name) ?>">

  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= e($full_title) ?>">
  <meta name="twitter:description" content="<?= e($page_description) ?>">
  <meta name="twitter:image"       content="<?= e($og_image) ?>">

  <?php
  $schemaGraph = [
      ['@type' => 'Organization', '@id' => $base_url . '/#organization', 'name' => $site_name, 'url' => $base_url,
       'logo' => $base_url . '/assets/images/logo.avif',
       'sameAs' => array_values(array_filter([$instagram ?? null, $facebook ?? null])),
       'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => $phone1_raw ? '+46' . ltrim($phone1_raw, '0') : null,
                           'contactType' => 'customer service', 'areaServed' => 'SE', 'availableLanguage' => ['sv', 'en']]],
      ['@type' => 'WebSite', '@id' => $base_url . '/#website', 'url' => $base_url, 'name' => $site_name,
       'publisher' => ['@id' => $base_url . '/#organization']],
      ['@type' => 'WebPage', '@id' => $page_canonical . '#webpage', 'url' => $page_canonical,
       'name' => $full_title, 'description' => $page_description, 'isPartOf' => ['@id' => $base_url . '/#website']],
  ];

  $schemaBusiness = [
      '@type' => 'LocalBusiness', '@id' => $base_url . '/#localbusiness', 'name' => 'M2 Bygg Team AB',
      'url' => $base_url, 'telephone' => ['+46319688880', '+46732405026'], 'email' => 'info@m2team.se',
      'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Lillhagsvägen 88', 'postalCode' => '442 43',
                     'addressLocality' => 'Hisings Backa', 'addressRegion' => 'Västra Götaland', 'addressCountry' => 'SE'],
      'geo' => ['@type' => 'GeoCoordinates', 'latitude' => 57.7420, 'longitude' => 11.9630],
  ];
  if ($schemaReviewCount > 0) {
      $schemaBusiness['aggregateRating'] = [
          '@type' => 'AggregateRating', 'ratingValue' => round($schemaAvgRating, 1), 'reviewCount' => $schemaReviewCount,
      ];
      $schemaBusiness['review'] = array_map(fn($r) => [
          '@type' => 'Review',
          'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (int)$r['rating'], 'bestRating' => 5],
          'author' => ['@type' => 'Person', 'name' => $r['customer_name']],
          'reviewBody' => $r['body'],
      ], $schemaTopReviews);
  }
  $schemaGraph[] = $schemaBusiness;

  // Optional per-page BreadcrumbList — pages set $breadcrumbs = [['Hem','/'], ['Tjänster','/tjanster'], ['Takbyte', null]] before including this file.
  if ($breadcrumbs) {
      $schemaGraph[] = [
          '@type' => 'BreadcrumbList',
          'itemListElement' => array_map(fn($b, $i) => array_filter([
              '@type' => 'ListItem', 'position' => $i + 1, 'name' => $b[0],
              'item' => $b[1] ? $base_url . $b[1] : null,
          ]), $breadcrumbs, array_keys($breadcrumbs)),
      ];
  }
  ?>
  <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $schemaGraph], JSON_UNESCAPED_UNICODE) ?></script>

  <!-- Critical CSS loads render-blocking (it's small and above-the-fold styling depends on it);
       fonts and anything non-critical are deferred below to keep first paint fast. -->
  <link rel="stylesheet" href="/css/main.css">
  <?= $extra_css ?? '' ?>

  <!-- LCP image preload — pages set $lcp_image (e.g. the hero background URL) before including this file -->
  <?php if (!empty($lcp_image)): ?>
  <link rel="preload" as="image" href="<?= e($lcp_image) ?>" fetchpriority="high">
  <?php endif; ?>
  <link rel="preconnect" href="https://images.unsplash.com">

  <!-- Google Fonts: Playfair Display (headlines) + Inter (body) — loaded non-blocking via the
       print-media swap trick so font CSS doesn't delay first paint; falls back to a noscript tag. -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap"></noscript>

  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="apple-touch-icon" href="/assets/images/logo.avif">
  <link rel="manifest" href="/site.webmanifest">

  <?php if (defined('GA4_MEASUREMENT_ID')): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(GA4_MEASUREMENT_ID) ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= e(GA4_MEASUREMENT_ID) ?>');
  </script>
  <?php endif; ?>
</head>
<body>

<style>.skip-link{position:absolute;left:-9999px;top:0;background:#1D1D1F;color:#fff;padding:10px 18px;border-radius:0 0 8px 0;z-index:9999;font-size:14px;font-weight:600;text-decoration:none}.skip-link:focus{left:0}</style>
<a href="#main-content" class="skip-link">Hoppa till innehåll</a>

<!-- FIXED DARK NAVIGATION (matching reference) -->
<nav class="nav" role="navigation" aria-label="Huvudmeny">
  <div class="container">
    <div class="nav__inner">

      <!-- Logo: "m2 / BYGG / TEAM" matching reference exactly -->
      <a href="/" class="nav__logo" aria-label="M2 Bygg Team – Startsida" style="display: inline-flex; align-items: center; text-decoration: none;">
  <img src="/assets/images/logo.png" alt="M2 Bygg Team" style="display: block; max-height: 40px; width: auto; height: auto;">
</a>

      <!-- Desktop links -->
      <ul class="nav__links" role="list">
        <li class="nav__item">
          <a href="/tjanster" class="<?= $active_page==='tjanster'?'active':'' ?>">
            Tjänster
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          </a>
          <div class="nav__dropdown nav__dropdown--cols">
            <?php foreach ($nav_service_groups as $groupName => $navGroupItems): ?>
            <div class="nav__dropdown-col">
              <div class="nav__dropdown-heading"><?= e($groupName) ?></div>
              <ul role="list">
                <?php foreach ($navGroupItems as $s): ?>
                <li><a href="<?= e($s[1]) ?>"><?= e($s[0]) ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endforeach; ?>
          </div>
        </li>
        
        <li><a href="/galleri" class="<?= $active_page==='galleri'?'active':'' ?>" <?= $active_page==='galleri'?'aria-current="page"':'' ?>>Galleri</a></li>
        <li><a href="/fastighet" class="<?= $active_page==='fastighet'?'active':'' ?>" <?= $active_page==='fastighet'?'aria-current="page"':'' ?>>För fastigheter</a></li>
        <li><a href="/kontakt" class="<?= $active_page==='kontakt'?'active':'' ?>" <?= $active_page==='kontakt'?'aria-current="page"':'' ?>>Kontakt</a></li>
      </ul>

      <div class="nav__cta">
        <a href="tel:<?= e($phone1_raw) ?>" class="nav__phone">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          <?= e($phone1) ?>
        </a>
        <a href="/offert" class="btn btn--outline-dark btn--sm">Få offert</a>
      </div>

      <button class="nav__burger" aria-label="Öppna meny">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="nav__overlay"></div>
<div class="nav__mobile" role="dialog" aria-label="Mobilmeny">
  <ul>
    <li>
      <button class="nav__mobile-toggle">Tjänster
        <svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 1l7 7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
      <ul class="nav__mobile-sub">
        <?php foreach ($nav_services as $s): ?><li><a href="<?= e($s[1]) ?>"><?= e($s[0]) ?></a></li><?php endforeach; ?>
      </ul>
    </li>
    <li><a href="/om-oss">Om oss</a></li>
    <li><a href="/galleri">Galleri</a></li>
    <li><a href="/fastighet">För fastigheter</a></li>
    <li><a href="/projekt">Projekt</a></li>
    <li><a href="/kontakt">Kontakt</a></li>
  </ul>
  <div style="display:flex;flex-direction:column;gap:10px;margin-top:32px">
    <a href="tel:<?= e($phone1_raw) ?>" class="btn btn--outline-dark" style="justify-content:center;border-color:rgba(255,255,255,.3)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
      <?= e($phone1) ?>
    </a>
    <a href="/offert" class="btn btn--primary" style="justify-content:center">Få kostnadsfri offert</a>
  </div>
</div>

<span id="main-content" tabindex="-1"></span>