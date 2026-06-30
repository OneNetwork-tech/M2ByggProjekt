<?php
$page_title = 'Tak- & Fasadtvätt Göteborg – Taktäckning och Fasadmålning';
$page_description = 'Taktäckning och fasadmålning i Göteborg. Taktvätt, fasadtvätt och målning på plåt och trä. Kostnadseffektivt skydd för ditt tak och fasad. M2 Bygg Team AB.';
$active_page = 'tak-fasadtvatt';
$breadcrumbs = [['Hem', '/'], ['Tak- & fasadtvätt', null]];
$lcp_image = '/uploads/site/tak-fasadtvatt.avif';
require_once __DIR__ . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Tak- &amp; fasadtvätt</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/site/tak-fasadtvatt.avif')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Taktäckning och fasadmålning</h1>
      <p class="animate-in delay-2">Färgen bleknar och ytor utsätts för påfrestningar från väder och vind – regelbundet underhåll är avgörande. Vi erbjuder taktvätt, fasadtvätt och målning på plåt och träsurfaces med kvalitetsförarbete.</p>
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
      <p class="eyebrow">Våra tjänster</p>
      <h2>Rengöring och målning</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:28px">
      <?php foreach([
        ['Taktvätt', '/tak-fasadtvatt/taktvatt', 'Tar bort mossa, alger och smuts som skadar materialet och förkortar livslängden – ett kostnadseffektivt sätt att skydda ditt tak.'],
        ['Fasadtvätt', '/tak-fasadtvatt/fasadtvatt', 'Avlägsnar smuts, alger och avgaser. Återställer husets utseende och ger bättre hållbarhet.'],
        ['Målning', null, 'Vi specialiserar oss på plåt- och träytor med kvalitetsförarbete – rengöring, skrapning och priming.'],
      ] as $s): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:32px">
        <h3 style="margin-bottom:10px"><?= e($s[0]) ?></h3>
        <p style="color:var(--steel);line-height:1.65;margin-bottom:20px;font-size:14.5px"><?= e($s[2]) ?></p>
        <?php if ($s[1]): ?><a href="<?= e($s[1]) ?>" class="btn btn--outline-light">Läs mer</a><?php endif; ?>
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
