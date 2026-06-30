<?php
$page_title = 'Klä in fasad Göteborg – Fasadbeklädnad till fast pris';
$page_description = 'Professionell infaskning och fasadbeklädnad i Göteborg. Träpanel, fibercementskivor och puts. Fast pris och ROT-avdrag. M2 Bygg Team AB.';
$active_page = 'tjanster';
$breadcrumbs = [['Hem', '/'], ['Tjänster', '/tjanster'], ['Klä in fasad Göteborg', null]];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/tjanster">Tjänster</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Klä in fasad</span>
</div></div></div>

<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/fasad-kladinfasad-1.jpg')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--gold-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in" style="margin-bottom:16px">Klä in fasad i Göteborg</h1>
      <p class="animate-in">Fasadbeklädnad som skyddar och förnyar ditt hus – träpanel, fibercementskivor eller puts. Vi utför hela projektet från rivning till färdig fasad med fast pris.</p>
      <ul style="list-style:none;margin:24px 0 28px;display:flex;flex-direction:column;gap:10px" class="animate-in">
        <?php foreach([
          'Träpanel, fibercementskivor och putsade fasader',
          'Komplett projekt – rivning, isolering och beklädnad',
          'ROT-avdrag på allt arbete',
          'Fast pris – inga överraskningar',
        ] as $p): ?>
        <li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--gold-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?= htmlspecialchars($p) ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="animate-in" style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="/offert" class="btn btn--primary btn--lg">Begär gratis offert</a>
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
      </div>
    </div>
  </div>
</section>

<section class="section" style="background:var(--white)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center">
      <div class="reveal">
        <img src="/uploads/fasad-kladinfasad-1.jpg"
             alt="Fasadbeklädnad – klä in fasad Göteborg"
             loading="lazy"
             style="width:100%;height:460px;object-fit:cover;border-radius:var(--r-xl);box-shadow:var(--shadow-xl)">
      </div>
      <div class="reveal">
        <span class="price-badge" style="margin-bottom:16px">från 350 kr/m²</span>
        <h2 style="margin-bottom:12px">Ny fasad som håller i decennier</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:28px">Att klä in fasaden är ett av de mest effektiva sätten att skydda och förnya ditt hus. Vi hjälper dig välja rätt material för din stil och budget.</p>
        <div style="display:flex;flex-direction:column;gap:14px" class="reveal-group">
          <?php foreach([
            ['Träpanel','Klassisk och naturlig. Fungerar utmärkt i Göteborgsklimatet med rätt behandling.'],
            ['Fibercementskivor','Underhållsfritt och väderbeständigt. Kräver minimal skötsel och håller decennier.'],
            ['Putsad fasad','Ger ett modernt och rent utseende. Bra isoleringsegenskaper.'],
          ] as $i => [$h, $p]): ?>
          <div class="reveal" style="display:flex;gap:13px;align-items:flex-start">
            <div style="min-width:34px;height:34px;background:rgba(201,168,76,.1);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:var(--font-display);font-weight:700;font-size:13px;color:var(--gold)">0<?= $i+1 ?></div>
            <div><h4 style="margin-bottom:3px"><?= htmlspecialchars($h) ?></h4><p style="font-size:14px;color:var(--steel);line-height:1.6"><?= htmlspecialchars($p) ?></p></div>
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

<section class="section" style="background:var(--surface)">
  <div class="container container--narrow">
    <div class="reveal" style="text-align:center;margin-bottom:40px">
      <span class="eyebrow">Vanliga frågor</span>
      <h2>Frågor om att klä in fasad</h2>
    </div>
    <?php foreach([
      ['Vad kostar det att klä in en fasad?','Priset varierar beroende på material och husets storlek. Träpanel kostar från 350 kr/m², fibercementskivor från 450 kr/m². Med ROT-avdrag minskar din kostnad med 30% av arbetskostnaden.'],
      ['Hur lång tid tar projektet?','En normal villa tar 5–10 arbetsdagar beroende på storlek och material. Vi ger dig en exakt tidsplan i offerten.'],
      ['Behövs bygglov?','Det beror på kommunen och materialet. Vi hjälper dig undersöka vad som gäller för just din fastighet och kan bistå med handlingarna.'],
      ['Kan jag klä in fasaden utan att byta isolering?','Ja, ofta kan befintlig isolering behållas. Vi gör alltid en besiktning för att bedöma skicket och rekommendera rätt åtgärd.'],
    ] as [$q, $a]): ?>
    <div class="faq-item reveal">
      <button class="faq-q"><?= htmlspecialchars($q) ?><div class="faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div></button>
      <div class="faq-a"><div class="faq-a-inner"><?= htmlspecialchars($a) ?></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap">
      <div class="reveal">
        <span class="eyebrow">Kom igång idag</span>
        <h2 style="margin:10px 0;color:var(--white)">Redo för ny fasad?</h2>
        <p>Gratis besiktning och offert inom 24 timmar.</p>
      </div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
        <a href="/offert" class="btn btn--primary btn--lg">Få kostnadsfri offert</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
