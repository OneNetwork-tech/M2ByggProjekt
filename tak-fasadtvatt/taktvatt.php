<?php
$page_title = 'Taktvätt Göteborg – Skydda Ditt Tak';
$page_description = 'Professionell taktvätt i Göteborg. Tar bort mossa, alger och smuts som skadar materialet och förkortar livslängden. M2 Bygg Team AB.';
$active_page = 'tak-fasadtvatt';
$breadcrumbs = [['Hem', '/'], ['Tak- & fasadtvätt', '/tak--fasadtvatt'], ['Taktvätt', null]];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/tak--fasadtvatt">Tak- &amp; fasadtvätt</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Taktvätt</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/site/tak-fasadtvatt.avif')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Taktvätt</h1>
      <p class="animate-in delay-2">Mossa, alger och smuts skadar takmaterialet och förkortar dess livslängd. Taktvätt är ett kostnadseffektivt sätt att skydda ditt tak och förlänga tiden till nästa stora investering.</p>
      <div class="animate-in delay-4" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
        <a href="/offert" class="btn btn--primary btn--lg">Begär gratis offert</a>
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
      </div>
    </div>
  </div>
</section>
<div class="trust-strip"><div class="container"><div class="trust-strip__grid">
<?php foreach([['Fast pris','Prisgaranti alltid'],['Kostnadseffektivt','Förlänger takets liv'],['Kostnadsfri offert','Svar inom 24h']] as $t): ?>
<div class="trust-item"><div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="trust-item__text"><strong><?=$t[0]?></strong><span><?=$t[1]?></span></div></div>
<?php endforeach; ?>
</div></div></div>
<section class="section">
  <div class="container container--narrow">
    <p style="color:var(--steel);line-height:1.8;margin-bottom:20px">Vi använder skonsamma och effektiva metoder för att ta bort smuts och mossa utan att skada takmaterialet.</p>
    <div style="margin-top:28px;display:flex;gap:10px;flex-wrap:wrap">
      <a href="/offert" class="btn btn--primary">Begär gratis offert</a>
      <a href="/tak--fasadtvatt" class="btn btn--outline-light">Se alla tjänster</a>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
