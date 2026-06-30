<?php
$page_title = 'Fastighetspartner Göteborg – Avtal för BRF, Fastighetsbolag & Förvaltare';
$page_description = 'M2 Bygg Team erbjuder ramavtal och löpande underhåll för BRF:er, fastighetsbolag, fastighetsägare och förvaltare i Göteborg och Västsverige. Prioriterad service och fast kontaktperson.';
$active_page = 'fastighet';
$breadcrumbs = [['Hem', '/'], ['För fastigheter', null]];
require_once __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>För fastigheter</span>
</div></div></div>

<!-- HERO -->
<section class="hero" style="padding:90px 0 80px;min-height:0">
 <!-- <div class="hero__bg" style="background-image:url('/uploads/fasad-renovering.jpg')"></div> -->
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:640px">
      <p class="eyebrow animate-in" style="color:var(--gold-lt);margin-bottom:18px">M2 för fastigheter</p>
      <h1 class="animate-in" style="margin-bottom:18px">En kontaktpunkt för hela fastighetsportföljen</h1>
      <p class="animate-in" style="color:rgba(245,245,247,.8);line-height:1.75;margin-bottom:28px">
        M2 är plattformen som samordnar underhåll, renovering och akuta insatser för BRF:er, fastighetsbolag och förvaltare. Ni hanterar era hyresgäster – vi hanterar resten.
      </p>
      <div class="animate-in" style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="/offert" class="btn btn--primary btn--lg">Begär avtalsinformation</a>
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
      </div>
    </div>
  </div>
</section>

<!-- TRUST STRIP -->
<div class="trust-strip">
  <div class="container">
    <div class="trust-strip__grid">
      <?php foreach([
        ['<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>','Ramavtal','Skräddarsytt för er portfölj'],
        ['<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>','Prioritet','Garanterad utföringstid'],
        ['<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>','Fast kontakt','En projektledare för allt'],
        ['<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>','Dokumentation','ROT & rapporter'],
      ] as [$icon,$title,$sub]): ?>
      <div class="trust-item">
        <div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $icon ?></svg></div>
        <div class="trust-item__text"><strong><?= htmlspecialchars($title) ?></strong><span><?= htmlspecialchars($sub) ?></span></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- WHO WE SERVE -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:48px">
      <span class="eyebrow">Vem vi hjälper</span>
      <h2>Vi samarbetar med hela fastighetssektorn</h2>
      <p style="color:var(--steel);max-width:540px;margin:12px auto 0">Oavsett om ni förvaltar en enstaka bostadsrättsförening eller en portfölj med hundratals lägenheter – vi har lösningen.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px" class="fastighet-who-grid reveal-group">
      <?php foreach([
        ['BRF','Bostadsrättsförening','Löpande underhåll av tak och fasad, protokoll till stämma, ROT-avdrag för föreningens räkning.','M 4 12h16M4 6h16M4 18h16'],
        ['Fastighetsbolag','Kommersiella aktörer','Ramavtal för era kommersiella fastigheter. Vi hanterar allt från enstaka jobb till helårsavtal med SLA.','M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z'],
        ['Fastighetsägare','Privata ägare','Ni äger, vi underhåller. Fast kontaktperson och löpande statusrapporter på era hyresfastigheter.','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
        ['Förvaltare','Tekniska förvaltare','Vi är er operativa arm. Snabb mobilisering, tydlig dokumentation och marknadsmässiga priser.','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
      ] as [$title,$sub,$desc,$icon]): ?>
      <div class="reveal" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-xl);padding:28px;display:flex;flex-direction:column;gap:14px">
        <div style="width:52px;height:52px;background:var(--gold-lt);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--gold-dk)" stroke-width="1.8" width="24" height="24"><path d="<?= $icon ?>"/></svg>
        </div>
        <div>
          <h3 style="font-size:1.1rem;margin-bottom:4px"><?= htmlspecialchars($title) ?></h3>
          <p style="font-size:.78rem;font-weight:600;color:var(--gold-dk);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px"><?= htmlspecialchars($sub) ?></p>
          <p style="font-size:.87rem;color:var(--steel);line-height:1.65"><?= htmlspecialchars($desc) ?></p>
        </div>
        <a href="/offert" style="margin-top:auto;color:var(--gold-dk);font-size:.83rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px">
          Kom igång
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SERVICES FOR FASTIGHET -->
<section class="section" style="background:var(--surface)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:48px">
      <span class="eyebrow">Tjänster</span>
      <h2>Vad vi utför åt er</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px" class="fastighet-services-grid reveal-group">
      <?php foreach([
        ['Takunderhåll','Taktvätt, takmålning, takbyte och plåtarbeten. Vi dokumenterar skicket och rekommenderar åtgärder proaktivt.','/tjanster/takbyte','<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['Fasadunderhåll','Fasadtvätt, fasadmålning, fasadrenovering och klä in fasad. Vi håller era byggnaders skal i toppskick.','/tjanster/fasadmalning','<rect x="2" y="3" width="20" height="14" rx="2"/>'],
        ['Balkong & räcken','Rostskydd, måling och träolja på balkonger och räcken. Obligatorisk besiktning vart 6:e år för BRF:er.','/tjanster/balkongmalning','<path d="M3 3h18v4H3zM3 10h18v4H3zM3 17h18v4H3z"/>'],
        ['Klottersanering','Snabb och effektiv borttagning av klotter. Vi erbjuder även abonnemang med garanterad responstid.','/tjanster/klottersanering','<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
        ['Stenläggning & mark','Markarbeten, stenläggning, kantsten och dränering. Vi sköter utemiljön kring era fastigheter.','/tjanster/stenlaggning','<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>'],
        ['Plåtarbeten','Hängrännor, stuprör, beslag och plåtdetaljer. Förebyggande underhåll minskar risken för vattenskador.','/tjanster/platarbeten','<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
      ] as [$title,$desc,$href,$icon]): ?>
      <a href="<?= htmlspecialchars($href) ?>" class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:26px;text-decoration:none;display:flex;flex-direction:column;gap:12px;transition:box-shadow .25s,transform .25s,border-color .25s" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-3px)';this.style.borderColor='var(--gold-lt)'" onmouseout="this.style.boxShadow='';this.style.transform='';this.style.borderColor=''">
        <div style="width:44px;height:44px;background:var(--gold-lt);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--gold-dk)" stroke-width="1.8" width="20" height="20"><?= $icon ?></svg>
        </div>
        <h4 style="color:var(--coal);margin-bottom:4px"><?= htmlspecialchars($title) ?></h4>
        <p style="font-size:.84rem;color:var(--steel);line-height:1.65;flex:1"><?= htmlspecialchars($desc) ?></p>
        <span style="font-size:.8rem;font-weight:600;color:var(--gold-dk);display:flex;align-items:center;gap:4px">
          Läs mer
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" style="background:var(--coal)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:48px">
      <span class="eyebrow" style="color:var(--gold-lt)">Processen</span>
      <h2 style="color:var(--white)">Så fungerar ett fastighetsavtal</h2>
    </div>
    <div class="steps-row reveal-group">
      <?php foreach([
        ['01','Kontakt & besiktning','Vi bokar ett kostnadsfritt besök för att inspektera era fastigheter och förstå era behov.'],
        ['02','Avtalsförslag','Vi skickar ett skräddarsytt avtalsförslag med fasta priser, prioriteter och tidsplaner.'],
        ['03','Löpande underhåll','Vi utför avtalade arbeten proaktivt – ni behöver inte ringa. Vi hör av oss när det är dags.'],
        ['04','Rapport & uppföljning','Efter varje åtgärd får ni en tydlig rapport med foton och faktura. Inga dolda kostnader.'],
      ] as [$num,$h,$p]): ?>
      <div class="step reveal">
        <div class="step__num"><?= $num ?></div>
        <h4 style="color:var(--white);margin:14px 0 8px"><?= htmlspecialchars($h) ?></h4>
        <p style="font-size:.85rem;color:rgba(245,245,247,.6);line-height:1.65"><?= htmlspecialchars($p) ?></p>
      </div>
      <div class="step-arrow" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="rgba(201,168,76,.5)" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap">
      <div class="reveal">
        <span class="eyebrow">Bli avtalspartner</span>
        <h2 style="margin:10px 0;color:var(--white)">Redo att teckna avtal?</h2>
        <p>Vi kontaktar er inom 24 timmar för ett kostnadsfritt besök.</p>
      </div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
        <a href="/offert" class="btn btn--primary btn--lg">Begär avtalsinformation</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
