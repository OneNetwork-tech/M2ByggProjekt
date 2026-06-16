<?php
$page_title       = 'Prisguide 2025 – Tak, Fasad & Mark Göteborg';
$page_description = 'Komplett prisguide 2025 för tak, fasad och markarbeten i Göteborg. Verkliga priser med och utan ROT-avdrag. M2 Bygg Team AB.';
$active_page      = 'prisguide';
require_once __DIR__ . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Prisguide 2025</span>
</div></div></div>

<section class="hero" style="padding:76px 0 64px;text-align:center">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:580px;margin:0 auto">
      <span class="badge badge--copper animate-in" style="margin:0 auto 18px;display:inline-flex">Uppdaterat juni 2025</span>
      <h1 class="animate-in delay-1" style="margin-bottom:14px">Prisguide 2025</h1>
      <p class="animate-in delay-2">Verkliga priser på alla tjänster i Göteborg och Västsverige – med och utan ROT-avdrag.</p>
    </div>
  </div>
</section>

<div style="background:rgba(181,113,42,.08);border-bottom:1px solid rgba(181,113,42,.18);padding:16px 0">
  <div class="container">
    <div style="display:flex;align-items:flex-start;gap:14px;max-width:760px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;color:var(--copper);flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <p style="font-size:14px;color:var(--steel);line-height:1.65">Priserna är riktvärden baserade på verkliga projekt. Exakt pris ges alltid i skriftlig offert efter gratis besiktning. <strong>ROT-avdrag:</strong> 30% av arbetskostnaden (ej material), max 50 000 kr/person/år. Priserna "inkl. ROT" förutsätter att 50% av totalkostnaden är arbete.</p>
    </div>
  </div>
</div>

<div style="max-width:1240px;margin:0 auto;padding:52px 32px;display:grid;grid-template-columns:1fr 280px;gap:48px;align-items:start">
  <div>
    <?php
    $sections = [
      ['Takarbeten', 'tak', '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
        ['header'=>['Taktyp','Pris/m²','Inkl. ROT/m²','Livslängd'],
         'rows'=>[
          ['Tegeltak','900–1 400 kr','770–1 200 kr','50–100 år','green'],
          ['Betongpannor','750–1 100 kr','640–940 kr','30–50 år','green'],
          ['Plåttak (stål)','850–1 300 kr','720–1 100 kr','40–60 år','green'],
          ['Plåttak (zink)','1 100–1 700 kr','940–1 450 kr','60–80 år','green'],
          ['Papptak','500–850 kr','425–720 kr','20–30 år','green'],
         ],'note'=>'Takbyte inkl. demontering, underlag, undertäck och ytmaterial.'],
        ['header'=>['Tjänst','Pris/m²','Inkl. ROT/m²','Hållbarhet'],
         'rows'=>[
          ['Takmålning','150–280 kr','120–238 kr','8–12 år','green'],
          ['Taktvätt + mossbehandling','80–160 kr','68–136 kr','3–5 år','green'],
          ['Takrenovering (partiell)','Offert','Offert','Beror på skada','steel'],
         ],'note'=>'Takmålning inkluderar alltid tvätt och primerbehandling.'],
        ['header'=>['Plåttjänst','Pris','Inkl. ROT','Enhet'],
         'rows'=>[
          ['Hängrännor (per löpmeter)','350–650 kr','300–555 kr','Stål/Zink/Alu',''],
          ['Stuprör (per st)','1 500–3 500 kr','1 275–2 975 kr','Stål/Zink',''],
          ['Plåtbeslag takfot (löpmeter)','250–500 kr','212–425 kr','Stål/Zink',''],
         ],'note'=>'Priser inklusive material och montering.'],
      ],
      ['Fasadarbeten', 'fasad', '<rect x="2" y="3" width="20" height="14" rx="2"/>',
        ['header'=>['Fasadtyp','Pris/m²','Inkl. ROT/m²','Hållbarhet'],
         'rows'=>[
          ['Träfasad (panel)','200–320 kr','170–272 kr','8–12 år','green'],
          ['Putsfasad','180–280 kr','153–238 kr','10–15 år','green'],
          ['Tegelfasad','150–250 kr','127–212 kr','15–20 år','green'],
          ['Skivfasad','200–300 kr','170–255 kr','8–12 år','green'],
         ],'note'=>'Fasadmålning inkluderar alltid tvätt, grundning och målning.'],
        ['header'=>['Tjänst','Pris/m²','Inkl. ROT/m²','Hållbarhet'],
         'rows'=>[
          ['Fasadrenovering (komplett)','250–450 kr','212–382 kr','15–20 år','green'],
          ['Fasadtvätt + biocid','100–200 kr','85–170 kr','5–8 år','green'],
          ['Lagning sprickor (puts)','Offert','Offert','Beror på skada','steel'],
         ],'note'=>'Fasadrenovering inkl. tvätt, lagning, grundning och finish.'],
      ],
      ['Mark & Övrigt', 'mark', '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>',
        ['header'=>['Tjänst','Pris','Inkl. ROT','Enhet'],
         'rows'=>[
          ['Stenläggning (betongsten)','500–800 kr','425–680 kr','per m²',''],
          ['Stenläggning (natursten)','900–1 500 kr','765–1 275 kr','per m²',''],
          ['Terrassplattor','600–1 100 kr','510–935 kr','per m²',''],
          ['Markarbete / schaktning','Offert','Offert','per projekt',''],
          ['Klottersanering','Offert','Offert','per m²',''],
         ],'note'=>'Stenläggning inkl. underlagsarbete och dränering. Exakt pris beror på platsens förutsättningar.'],
      ],
    ];

    foreach ($sections as $section):
      $title  = $section[0];
      $id     = $section[1];
      $icon   = $section[2];
      $tables = array_slice($section, 3); ?>
    <div id="<?= $id ?>" style="margin-bottom:52px;scroll-margin-top:90px">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid var(--copper)">
        <div style="width:44px;height:44px;background:rgba(181,113,42,.1);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:21px;height:21px;color:var(--copper)"><?= $icon ?></svg>
        </div>
        <h2 style="font-size:1.6rem"><?= $title ?></h2>
      </div>

      <?php foreach ($tables as $table): ?>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;margin-bottom:14px" class="reveal">
        <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;background:rgba(181,113,42,.07);border-bottom:1px solid var(--border)">
          <?php foreach ($table['header'] as $h): ?>
          <div style="padding:11px 16px;font-size:11px;font-weight:600;color:var(--copper);letter-spacing:.07em;text-transform:uppercase"><?= $h ?></div>
          <?php endforeach; ?>
        </div>
        <?php foreach ($table['rows'] as $r): ?>
        <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;border-bottom:1px solid rgba(0,0,0,.04);transition:background .1s" onmouseover="this.style.background='rgba(0,0,0,.02)'" onmouseout="this.style.background='transparent'">
          <div style="padding:12px 16px;font-size:14px;font-weight:500;color:var(--coal)"><?= $r[0] ?></div>
          <div style="padding:12px 16px;font-family:var(--font-display);font-weight:600;font-size:13.5px;color:var(--steel)"><?= $r[1] ?></div>
          <div style="padding:12px 16px;font-family:var(--font-display);font-weight:600;font-size:13.5px;color:<?= $r[4]==='green'?'#059669':'var(--steel)' ?>"><?= $r[2] ?></div>
          <div style="padding:12px 16px;font-size:13px;color:var(--copper)"><?= $r[3] ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($table['note'])): ?>
        <div style="padding:10px 16px;background:rgba(0,0,0,.02);font-size:12.5px;color:var(--steel-lt)"><?= $table['note'] ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- Project examples -->
    <div id="exempel" style="scroll-margin-top:90px">
      <h2 style="font-size:1.6rem;margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid var(--copper)">Verkliga projektexempel</h2>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden" class="reveal">
        <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;background:rgba(181,113,42,.07);border-bottom:1px solid var(--border)">
          <?php foreach(['Projekt','Yta','Totalt','Inkl. ROT'] as $h): ?>
          <div style="padding:11px 16px;font-size:11px;font-weight:600;color:var(--copper);letter-spacing:.07em;text-transform:uppercase"><?= $h ?></div>
          <?php endforeach; ?>
        </div>
        <?php foreach([
          ['Takbyte tegeltak – Kungsbacka','165 m²','195 000 kr','148 000 kr'],
          ['Fasadmålning träfasad – Mölndal','220 m²','58 000 kr','45 000 kr'],
          ['Takbyte betongpannor – Hovås','140 m²','158 000 kr','122 000 kr'],
          ['Takmålning – Lerum','130 m²','28 000 kr','22 000 kr'],
          ['Fasadrenovering puts – Kungälv','180 m²','72 000 kr','55 000 kr'],
          ['Hängrännor – Alingsås','48 löpmeter','24 000 kr','19 000 kr'],
          ['Stenläggning terrass – Askim','45 m²','38 000 kr','30 000 kr'],
        ] as $r): ?>
        <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;border-bottom:1px solid rgba(0,0,0,.04);transition:background .1s" onmouseover="this.style.background='rgba(0,0,0,.02)'" onmouseout="this.style.background='transparent'">
          <div style="padding:12px 16px;font-size:14px;color:var(--coal)"><?= $r[0] ?></div>
          <div style="padding:12px 16px;font-family:var(--font-display);font-weight:600;font-size:13px;color:var(--steel)"><?= $r[1] ?></div>
          <div style="padding:12px 16px;font-family:var(--font-display);font-weight:600;font-size:13px;color:var(--steel)"><?= $r[2] ?></div>
          <div style="padding:12px 16px;font-family:var(--font-display);font-weight:600;font-size:13px;color:#059669"><?= $r[3] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <aside style="position:sticky;top:90px;display:flex;flex-direction:column;gap:14px">
    <div style="background:linear-gradient(135deg,rgba(181,113,42,.12),rgba(181,113,42,.04));border:1px solid rgba(181,113,42,.2);border-radius:var(--r-xl);padding:22px">
      <h4 style="margin-bottom:7px">Få exakt pris</h4>
      <p style="font-size:13.5px;color:var(--steel);line-height:1.6;margin-bottom:16px">Prisguiden är riktvärden. Gratis besiktning och fast pris – kontakta oss.</p>
      <a href="/offert" class="btn btn--copper" style="width:100%;justify-content:center">Begär offert</a>
    </div>
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
      <h4 style="margin-bottom:12px">Hoppa till</h4>
      <?php foreach([['#tak','Takarbeten'],['#fasad','Fasadarbeten'],['#mark','Mark & Övrigt'],['#exempel','Projektexempel']] as $l): ?>
      <a href="<?= $l[0] ?>" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:var(--r-md);font-size:13.5px;color:var(--steel);transition:all .15s;margin-bottom:2px" onmouseover="this.style.color='var(--coal)';this.style.background='var(--sand-lt)'" onmouseout="this.style.color='var(--steel)';this.style.background='transparent'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper)"><path d="M9 18l6-6-6-6"/></svg>
        <?= $l[1] ?>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
      <h4 style="margin-bottom:10px">Ring oss</h4>
      <a href="tel:031968888" style="font-family:var(--font-display);font-weight:700;font-size:20px;color:var(--coal);letter-spacing:-0.02em;display:block;margin-bottom:4px">031-96 88 88</a>
      <a href="tel:0732405026" style="font-size:15px;color:var(--steel-lt);display:block;margin-bottom:8px">0732-40 50 26</a>
      <div style="font-size:12.5px;color:var(--steel-lt)">Mån–Fre 07:00–18:00</div>
    </div>
  </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
