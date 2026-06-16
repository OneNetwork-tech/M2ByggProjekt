<?php
$page_title = 'Taktvätt Göteborg';
$page_description = 'Professionell taktvätt i Göteborg till fast pris. Högtryckstvätt och mossbehandling. Förlänger takets livslängd. M2 Bygg Team AB.';
$active_page = 'tjanster';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/tjanster">Tjänster</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Taktvätt Göteborg</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Taktvätt i Göteborg</h1>
      <p class="animate-in delay-2">Högtryckstvätt och mossbehandling av alla taktyper. Förhindrar fuktskador och förlänger takets livslängd med 5–10 år. Fast pris, ROT-avdrag.</p>
      <ul style="list-style:none;margin:24px 0 28px;display:flex;flex-direction:column;gap:10px" class="animate-in delay-3"><li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Förlänger takets livslängd med 5–10 år</li>
<li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Högtryckstvätt och mossbehandling ingår</li>
<li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Förhindrar fuktskador och dyr reparation</li>
<li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>ROT-avdrag gäller på taktvätt</li></ul>
      <div class="animate-in delay-4" style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="/offert" class="btn btn--primary btn--lg">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
          Begär gratis offert
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
<?php foreach([['Fast pris','Prisgaranti alltid'],['ROT-avdrag','Vi hanterar ansökan'],['5 år garanti','På allt arbete'],['Svar inom 24h','Gratis besiktning']] as $t): ?>
<div class="trust-item"><div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="trust-item__text"><strong><?=$t[0]?></strong><span><?=$t[1]?></span></div></div>
<?php endforeach; ?>
</div></div></div>
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:68px;align-items:center">
      <div class="reveal">
        <img src="https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80" alt="Taktvätt Göteborg Göteborg" loading="lazy" style="width:100%;height:460px;object-fit:cover;border-radius:var(--r-xl);box-shadow:var(--shadow-xl)">
      </div>
      <div class="reveal">
        <span class="price-badge" style="margin-bottom:16px">från 80 kr/m²</span>
        <h2 style="margin-bottom:12px">Skydda taket – spara pengar</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:28px">Mossa och alger kan på 3–5 år orsaka fuktskador som kostar tio gånger mer än en taktvätt. Vi rekommenderar tvätt var 3–5 år.</p>
        <div style="display:flex;flex-direction:column;gap:14px" class="reveal-group">
<?php foreach([['Fast pris alltid','Det pris vi offererar är det du betalar. Prisgaranti ingår.'],['5 år garanti','Allt arbete täcks av 5 år garanti. Uppstår problem åtgärdar vi kostnadsfritt.'],['ROT-avdrag','30% av arbetskostnaden tillbaka. Vi hanterar ansökan till Skatteverket.']] as $i=>[$h,$p]): ?>
<div class="reveal" style="display:flex;gap:13px;align-items:flex-start">
  <div style="min-width:34px;height:34px;background:rgba(181,113,42,.1);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:var(--font-display);font-weight:700;font-size:13px;color:var(--copper)">0<?=$i+1?></div>
  <div><h4 style="margin-bottom:3px"><?=$h?></h4><p style="font-size:14px;color:var(--steel);line-height:1.6"><?=$p?></p></div>
</div>
<?php endforeach; ?>
        </div>
        <div style="margin-top:28px;display:flex;gap:10px;flex-wrap:wrap">
          <a href="/offert" class="btn btn--primary">Begär gratis offert</a>
          <a href="/prisguide" class="btn btn--outline-light">Se prisguide</a>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="section" style="background:var(--sand-lt)">
  <div class="container container--narrow">
    <div class="section-header reveal" style="margin-bottom:28px">
      <p class="eyebrow">Vanliga frågor</p>
      <h2>Frågor om taktvätt göteborg</h2>
    </div>
    <div class="faq-item reveal"><button class="faq-q">Vad kostar taktvätt i Göteborg?<div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button><div class="faq-a"><div class="faq-a-inner">Taktvätt kostar normalt 80–160 kr/m² inklusive mossbehandling. En normal villa kostar ca 8 000–20 000 kr. ROT-avdrag gäller.</div></div></div>
<div class="faq-item reveal"><button class="faq-q">Hur ofta ska man tvätta taket?<div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button><div class="faq-a"><div class="faq-a-inner">I Göteborgs fuktiga klimat rekommenderar vi taktvätt var 3–5 år. Ser du tydlig mossväxt är det dags omgående – mossa skadar tätningsskiktet och orsakar läckor.</div></div></div>
<div class="faq-item reveal"><button class="faq-q">Kan taktvätt skada taket?<div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button><div class="faq-a"><div class="faq-a-inner">Inte om det görs rätt. Vi anpassar alltid vattentrycket efter taktypen. För tegeltak och betongpannor används lägre tryck för att undvika sprickbildning.</div></div></div>
  </div>
</section>
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal"><p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Kom igång idag</p><h2 style="margin-bottom:8px">Redo för ett fast pris?</h2><p>Gratis besiktning och offert inom 24 timmar.</p></div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>031-96 88 88</a>
        <a href="/offert" class="btn btn--primary btn--lg"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>Begär gratis offert</a>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>