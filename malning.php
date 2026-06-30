<?php
$page_title = 'Målning Göteborg – Fasad, Tak & Inomhusmålning';
$page_description = 'Professionell målning i Göteborg – fasadmålning, takmålning, inomhusmålning och övrig målning. Rätt utförd målning skyddar, förskönar och lyfter helhetsintrycket. M2 Bygg Team AB.';
$active_page = 'malning';
$breadcrumbs = [['Hem', '/'], ['Målning', null]];
$lcp_image = '/uploads/site/malning.avif';
require_once __DIR__ . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Målning</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/site/malning.avif')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Målning</h1>
      <p class="animate-in delay-2">Rätt utförd målning gör mer än att bara förnya – den skyddar, förskönar och lyfter helhetsintrycket. Vi målar fasader, tak, snickerier och invändiga ytor med gediget förarbete och hög finish.</p>
      <div class="animate-in delay-4" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
        <a href="/offert" class="btn btn--primary btn--lg">Begär gratis offert</a>
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
      </div>
    </div>
  </div>
</section>
<div class="trust-strip"><div class="container"><div class="trust-strip__grid">
<?php foreach([['Fast pris','Prisgaranti alltid'],['ROT-avdrag','Vi hanterar ansökan'],['Kostnadsfri offert','Svar inom 24h']] as $t): ?>
<div class="trust-item"><div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="trust-item__text"><strong><?=$t[0]?></strong><span><?=$t[1]?></span></div></div>
<?php endforeach; ?>
</div></div></div>
<section class="section">
  <div class="container">
    <div class="section-header reveal" style="margin-bottom:40px;text-align:center">
      <p class="eyebrow">Våra måleritjänster</p>
      <h2>Fyra områden, en hög standard</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px">
      <?php foreach([
        ['Fasadmålning', '/malning/fasadmalning', 'Skyddar och förnyar husets exteriör med rätt färgval och gediget förarbete.'],
        ['Takmålning', '/malning/takmalning', 'Förlänger takets livslängd och ger fastigheten ett lyft.'],
        ['Inomhusmålning', '/malning/inomhusmalning', 'Väggar, tak, snickerier och dörrar – för hela hemmet eller enskilda rum.'],
        ['Övrig målning', '/malning/ovrig-malning', 'Staket, fönsterkarmar, plank, garageportar, förråd och andra detaljer.'],
      ] as $s): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:26px;transition:all .3s var(--ease-out)">
        <h3 style="margin-bottom:8px;font-size:1.1rem"><?= e($s[0]) ?></h3>
        <p style="color:var(--steel);line-height:1.6;margin-bottom:18px;font-size:14px"><?= e($s[2]) ?></p>
        <a href="<?= e($s[1]) ?>" class="btn btn--outline-light" style="font-size:13.5px">Läs mer</a>
      </div>
      <?php endforeach; ?>
    </div>
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
