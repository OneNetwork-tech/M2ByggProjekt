<?php
$page_title = 'Renovering Göteborg – Fasadarbete, Panelbyte & Tillbyggnad';
$page_description = 'Renovering i Göteborg – fasadarbete, panelbyten, plåtarbeten och om- & tillbyggnation. Vi renoverar med omtanke om funktion, estetik och byggnadens ursprungliga stil. M2 Bygg Team AB.';
$active_page = 'renovering';
$breadcrumbs = [['Hem', '/'], ['Renovering', null]];
$lcp_image = '/uploads/site/renovering.avif';
require_once __DIR__ . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Renovering</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/site/renovering.avif')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Renovering</h1>
      <p class="animate-in delay-2">Vi erbjuder anpassade renoveringstjänster för lägenheter, villor och kommersiella fastigheter. Vi renoverar med omtanke om både funktion, estetik och byggnadens ursprungliga stil.</p>
      <div class="animate-in delay-4" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
        <a href="/offert" class="btn btn--primary btn--lg">Begär gratis offert</a>
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
      </div>
    </div>
  </div>
</section>
<div class="trust-strip"><div class="container"><div class="trust-strip__grid">
<?php foreach([['Fast pris','Prisgaranti alltid'],['ROT-avdrag','Vi hanterar ansökan'],['Tydlig tidplan','Vi håller budget']] as $t): ?>
<div class="trust-item"><div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="trust-item__text"><strong><?=$t[0]?></strong><span><?=$t[1]?></span></div></div>
<?php endforeach; ?>
</div></div></div>
<section class="section">
  <div class="container">
    <div class="section-header reveal" style="margin-bottom:40px;text-align:center">
      <p class="eyebrow">Våra renoveringstjänster</p>
      <h2>Fyra områden vi tar hand om</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:28px">
      <?php foreach([
        ['Fasadarbete', 'Professionellt fasadarbete som förlänger livslängden samtidigt som det förbättrar utseende och funktion.'],
        ['Panelbyten', 'Byte av träpanel med fokus på hållbarhet och rätt materialval för ditt hus.'],
        ['Plåtarbeten', 'Precisionsarbete med plåt som skyddar tak och fasad – fönsterbleck, hängrännor och anpassade lösningar.'],
        ['Om- & tillbyggnation', 'Om- och tillbyggnader anpassade efter dina behov och byggnadens förutsättningar.'],
      ] as $s): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:32px">
        <h3 style="margin-bottom:10px"><?= e($s[0]) ?></h3>
        <p style="color:var(--steel);line-height:1.65;font-size:14.5px"><?= e($s[1]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section" style="background:var(--sand-lt)">
  <div class="container container--narrow" style="text-align:center">
    <p style="color:var(--steel);line-height:1.8;font-size:16px">Vår styrka ligger i noggrant utfört arbete, tydlig kommunikation och att alltid hålla tidsplan och budget.</p>
  </div>
</section>
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal"><p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Kom igång idag</p><h2 style="margin-bottom:8px">Redo för ett fast pris?</h2><p>Kostnadsfri offert inom 24 timmar.</p></div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
        <a href="/offert" class="btn btn--primary btn--lg">Begär gratis offert</a>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
