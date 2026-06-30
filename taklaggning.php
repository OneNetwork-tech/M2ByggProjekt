<?php
$page_title = 'Takläggning Göteborg – Betongtak, Plåttak & Papptak';
$page_description = 'Trygg och kvalitativ takläggning i Göteborg med material anpassade för svenska förhållanden. Betongtak, plåttak och papptak. Kostnadsfri takinspektion. M2 Bygg Team AB.';
$active_page = 'taklaggning';
$breadcrumbs = [['Hem', '/'], ['Takläggning', null]];
$lcp_image = '/uploads/site/taklaggning.avif';
require_once __DIR__ . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Takläggning</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/site/taklaggning.avif')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Takläggning</h1>
      <p class="animate-in delay-2">Taket är en av husets viktigaste delar – det skyddar inte bara mot väder och vind, utan påverkar också både utseendet och värdet på din fastighet. Vi erbjuder trygg och kvalitativ takläggning, med material och lösningar som är anpassade efter svenska förhållanden.</p>
      <div class="animate-in delay-4" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
        <a href="/offert" class="btn btn--primary btn--lg">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
          Kostnadsfri takinspektion
        </a>
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          031-96 88 88
        </a>
      </div>
    </div>
  </div>
</section>
<div class="trust-strip"><div class="container"><div class="trust-strip__grid">
<?php foreach([['Fast pris','Prisgaranti alltid'],['ROT-avdrag','Vi hanterar ansökan'],['Kostnadsfri besiktning','Svar inom 24h']] as $t): ?>
<div class="trust-item"><div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="trust-item__text"><strong><?=$t[0]?></strong><span><?=$t[1]?></span></div></div>
<?php endforeach; ?>
</div></div></div>
<section class="section">
  <div class="container">
    <div class="section-header reveal" style="margin-bottom:40px;text-align:center">
      <p class="eyebrow">Våra taklösningar</p>
      <h2>Tre material, samma kvalitet</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:28px">
      <?php foreach([
        ['Betongtak', '/taklaggning/betongtak', 'Ett hållbart och prisvärt tak som passar många byggnader, med lång livslängd och minimalt underhåll.'],
        ['Plåttak', '/taklaggning/plattak', 'Ett tak som kombinerar låg vikt med hög hållbarhet – passar både moderna och klassiska hus.'],
        ['Papptak', '/taklaggning/papptak', 'Vi hanterar både små och stora renoveringsprojekt, inklusive fasadarbete och takbyten.'],
      ] as $s): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:32px;transition:all .3s var(--ease-out)">
        <h3 style="margin-bottom:10px"><?= e($s[0]) ?></h3>
        <p style="color:var(--steel);line-height:1.65;margin-bottom:20px;font-size:14.5px"><?= e($s[2]) ?></p>
        <a href="<?= e($s[1]) ?>" class="btn btn--outline-light">Läs mer</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section" style="background:var(--sand-lt)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:68px;align-items:center">
      <div class="reveal">
        <h2 style="margin-bottom:12px">Rivning och underarbete ingår</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:20px">Innan nytt tak läggs kontrollerar vi alltid underlaget noggrant. Rivning av gammalt material, reparation av råspont och korrekt underarbete säkerställer att det nya taket håller i många år framöver.</p>
        <p style="color:var(--steel);line-height:1.72">Vi erbjuder kostnadsfri takinspektion innan arbetet påbörjas, så att du vet exakt vad som behöver göras och vad det kommer kosta – med fast pris.</p>
      </div>
      <div class="reveal">
        <img src="/uploads/gallery/taklaggning-efter.avif" alt="Färdigställd takläggning" loading="lazy" style="width:100%;height:380px;object-fit:cover;border-radius:var(--r-xl);box-shadow:var(--shadow-xl)">
      </div>
    </div>
  </div>
</section>
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal"><p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Kom igång idag</p><h2 style="margin-bottom:8px">Redo för ett fast pris?</h2><p>Kostnadsfri takinspektion och offert inom 24 timmar.</p></div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>031-96 88 88</a>
        <a href="/offert" class="btn btn--primary btn--lg"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>Begär gratis offert</a>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
