<?php
$page_title = 'Klottersanering Göteborg';
$page_description = 'Professionell klottersanering i Göteborg till fast pris. Snabb borttagning på alla underlag. Skyddsbehandling. M2 Bygg Team AB.';
$active_page = 'tjanster';
$breadcrumbs = [['Hem', '/'], ['Tjänster', '/tjanster'], ['Klottersanering Göteborg', null]];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/tjanster">Tjänster</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Klottersanering Göteborg</span>
</div></div></div>
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Klottersanering i Göteborg</h1>
      <p class="animate-in delay-2">Professionell klottersanering på alla underlag – tegel, betong, puts, metall och trä. Snabb och effektiv borttagning utan att skada underlaget, med skyddsbehandling mot återfall.</p>
      <ul style="list-style:none;margin:24px 0 28px;display:flex;flex-direction:column;gap:10px" class="animate-in delay-3"><li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Effektiv borttagning på alla underlag</li>
<li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Skyddsbehandling mot återfall ingår</li>
<li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Snabb respons – ofta inom 24h</li>
<li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Certifierade produkter och metoder</li></ul>
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
        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600&q=80" alt="Klottersanering Göteborg Göteborg" loading="lazy" style="width:100%;height:460px;object-fit:cover;border-radius:var(--r-xl);box-shadow:var(--shadow-xl)">
      </div>
      <div class="reveal">
        <span class="price-badge" style="margin-bottom:16px">Fast pris – offert efter besiktning</span>
        <h2 style="margin-bottom:12px">Snabb och diskret service</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:28px">Vi agerar snabbt. Klotter som åtgärdas inom 24–48 timmar ger lägre risk för återfall och är enklare att ta bort. Kontakta oss direkt.</p>
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
      <h2>Frågor om klottersanering göteborg</h2>
    </div>
    <div class="faq-item reveal"><button class="faq-q">Vad kostar klottersanering?<div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button><div class="faq-a"><div class="faq-a-inner">Priset beror på yta och underlag. Vi ger alltid ett fast pris efter bedömning. Kontakta oss via 031-96 88 88 för snabb service.</div></div></div>
<div class="faq-item reveal"><button class="faq-q">Kan klotter tas bort från alla ytor?<div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button><div class="faq-a"><div class="faq-a-inner">De flesta ytor går att sanera effektivt: puts, tegel, betong, metall, trä och glas. Porösa ytor kan kräva mer arbete. Vi bedömer alltid förutsättningarna.</div></div></div>
<div class="faq-item reveal"><button class="faq-q">Vad är skyddsbehandling mot klotter?<div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button><div class="faq-a"><div class="faq-a-inner">Skyddsbehandling är ett klotterskydd som appliceras efter sanering. Det gör framtida klotter lättare att ta bort och minskar skaderisken på underlaget.</div></div></div>
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