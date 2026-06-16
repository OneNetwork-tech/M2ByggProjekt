<?php
/**
 * M2 Bygg Team AB — Global Header
 * Design: Premium dark nav matching reference design.
 */
$site_name   = 'M2 Bygg Team AB';
$base_url    = 'https://www.m2team.se';
$phone1      = '031-96 88 88';
$phone1_raw  = '031968888';
$phone2      = '0732-40 50 26';
$phone2_raw  = '0732405026';
$email_addr  = 'info@m2team.se';
$address     = 'Lillhagsvägen 88, 442 43 Hisings Backa';
$instagram   = 'https://www.instagram.com/m2byggteam/';
$facebook    = 'https://www.facebook.com/profile.php?id=61577099783558';

$page_title       = $page_title       ?? $site_name;
$page_description = $page_description ?? 'M2 Bygg Team AB – Professionella tak- och fasadarbeten i Göteborg. Fast pris, ROT-avdrag och 5 år garanti.';
$page_canonical   = $page_canonical   ?? $base_url . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active_page      = $active_page      ?? '';
$og_image         = $og_image         ?? $base_url . '/img/og-default.jpg';
$full_title       = ($page_title === $site_name) ? $site_name : $page_title . ' | ' . $site_name;

$nav_services = [
  ['Takbyte',         '/tjanster/takbyte'],
  ['Takrenovering',   '/tjanster/takrenovering'],
  ['Takmålning',      '/tjanster/takmalning'],
  ['Taktvätt',        '/tjanster/taktvatt'],
  ['Plåtarbeten',     '/tjanster/platarbeten'],
  ['Fasadmålning',    '/tjanster/fasadmalning'],
  ['Fasadrenovering', '/tjanster/fasadrenovering'],
  ['Fasadtvätt',      '/tjanster/fasadtvatt'],
  ['Klä in fasad',    '/tjanster/kladinfasad'],
  ['Balkongmålning',  '/tjanster/balkongmalning'],
  ['Markarbete',      '/tjanster/markarbete'],
  ['Stenläggning',    '/tjanster/stenlaggning'],
  ['Klottersanering', '/tjanster/klottersanering'],
];

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($full_title) ?></title>
  <meta name="description" content="<?= e($page_description) ?>">
  <link rel="canonical" href="<?= e($page_canonical) ?>">

  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= e($page_canonical) ?>">
  <meta property="og:title"       content="<?= e($full_title) ?>">
  <meta property="og:description" content="<?= e($page_description) ?>">
  <meta property="og:image"       content="<?= e($og_image) ?>">
  <meta property="og:locale"      content="sv_SE">
  <meta property="og:site_name"   content="<?= e($site_name) ?>">

  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"LocalBusiness","name":"M2 Bygg Team AB","url":"https://www.m2team.se","telephone":["+46319688880","+46732405026"],"email":"info@m2team.se","address":{"@type":"PostalAddress","streetAddress":"Lillhagsvägen 88","postalCode":"442 43","addressLocality":"Hisings Backa","addressRegion":"Västra Götaland","addressCountry":"SE"},"geo":{"@type":"GeoCoordinates","latitude":57.7420,"longitude":11.9630},"aggregateRating":{"@type":"AggregateRating","ratingValue":"4.9","reviewCount":"1000"}}
  </script>

  <!-- Google Fonts: Playfair Display (headlines) + Inter (body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/css/main.css">
  <?= $extra_css ?? '' ?>

  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='4' fill='%231A1A1A'/><text x='16' y='23' text-anchor='middle' font-family='Georgia,serif' font-weight='900' font-size='16' fill='%23C9A84C'>m2</text></svg>">
</head>
<body>

<!-- FIXED DARK NAVIGATION (matching reference) -->
<nav class="nav" role="navigation" aria-label="Huvudmeny">
  <div class="container">
    <div class="nav__inner">

      <!-- Logo: "m2 / BYGG / TEAM" matching reference exactly -->
      <a href="/" class="nav__logo" aria-label="M2 Bygg Team – Startsida">
        <span class="nav__logo-mark">m2</span>
        <div class="nav__logo-sub">
          <span>Bygg</span>
          <span>Team</span>
        </div>
      </a>

      <!-- Desktop links -->
      <ul class="nav__links" role="list">
        <li class="nav__item">
          <a href="/tjanster" class="<?= $active_page==='tjanster'?'active':'' ?>">
            Tjänster
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          </a>
          <ul class="nav__dropdown" role="list">
            <?php foreach ($nav_services as $s): ?>
            <li><a href="<?= e($s[1]) ?>"><?= e($s[0]) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li><a href="/om-oss" class="<?= $active_page==='om-oss'?'active':'' ?>">Så fungerar det</a></li>
        <li><a href="/projekt" class="<?= $active_page==='projekt'?'active':'' ?>">Projekt</a></li>
        <li><a href="/om-oss#om-oss" class="<?= $active_page==='om-oss'?'active':'' ?>">Om oss</a></li>
        <li><a href="/kontakt" class="<?= $active_page==='kontakt'?'active':'' ?>">Kontakt</a></li>
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
    <li><a href="/om-oss">Så fungerar det</a></li>
    <li><a href="/projekt">Projekt</a></li>
    <li><a href="/om-oss#om-oss">Om oss</a></li>
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