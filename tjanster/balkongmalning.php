<?php
$page_title = 'Balkongmålning Göteborg – Räcke och balkong till fast pris';
$page_description = 'Professionell balkongmålning och räckesmålning i Göteborg. Rostskydd, träolja och lackering. Fast pris och ROT-avdrag. M2 Bygg Team AB.';
$active_page = 'tjanster';
$breadcrumbs = [['Hem', '/'], ['Tjänster', '/tjanster'], ['Balkongmålning Göteborg', null]];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/tjanster">Tjänster</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Balkongmålning</span>
</div></div></div>

<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('/uploads/fasad-balkong.jpg')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--gold-lt);margin-bottom:18px">Göteborg &amp; Västsverige</p>
      <h1 class="animate-in" style="margin-bottom:16px">Balkongmålning i Göteborg</h1>
      <p class="animate-in">Vi målar balkonger, räcken, trappor och uteplatser. Rätt behandling skyddar mot rost, fukt och UV-ljus och ger ett fräscht resultat som håller i många år.</p>
      <ul style="list-style:none;margin:24px 0 28px;display:flex;flex-direction:column;gap:10px" class="animate-in">
        <?php foreach([
          'Balkong, räcke, trappa och uteplats',
          'Rostskyddsbehandling för metall – järn och aluminium',
          'Träolja och lackning för trä',
          'ROT-avdrag och fast pris',
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
        <img src="/uploads/fasad-balkong.jpg"
             alt="Balkongmålning och räckesmålning Göteborg"
             loading="lazy"
             style="width:100%;height:460px;object-fit:cover;border-radius:var(--r-xl);box-shadow:var(--shadow-xl)">
      </div>
      <div class="reveal">
        <span class="price-badge" style="margin-bottom:16px">från 95 kr/lpm</span>
        <h2 style="margin-bottom:12px">Skyddat och snyggt i många år</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:28px">En ordentlig balkongmålning börjar alltid med grundlig rengöring, slipning och grundning. Vi använder produkter anpassade för utomhusbruk och Göteborgs väder.</p>
        <div style="display:flex;flex-direction:column;gap:14px" class="reveal-group">
          <?php foreach([
            ['Rostskyddsbehandling','Avrostning, grundning och lackering av järn- och aluminiumräcken som håller i 8–12 år.'],
            ['Träbehandling','Slipning, rengöring och träolja eller lackering beroende på trätyp och önskat resultat.'],
            ['Komplett balkong','Vi hanterar hela balkongen – golv, väggar, tak och räcke i ett sammanhängande projekt.'],
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
      <h2>Frågor om balkongmålning</h2>
    </div>
    <?php foreach([
      ['Vad kostar balkongmålning?','Räckesmålning kostar från 95 kr/lpm beroende på material och skick. En hel balkong inkl. golv, väggar och räcke offerteras per uppdrag. Med ROT-avdrag minskar du kostnaden med 30%.'],
      ['Hur lång tid tar det?','En balkong med räcke tar vanligtvis 1–2 dagar beroende på storlek och om grundning behövs. Vi ger dig exakt tidsplan i offerten.'],
      ['Vilket material målar ni?','Vi behandlar trä, järn, aluminium och betong. Varje material kräver rätt produkt och teknik för bästa hållbarhet.'],
      ['Ingår rengöring i priset?','Ja, grundlig rengöring och förpreparering ingår alltid. Det är avgörande för att målarfärgen ska hålla länge.'],
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
        <h2 style="margin:10px 0;color:var(--white)">Redo för fräsch balkong?</h2>
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
