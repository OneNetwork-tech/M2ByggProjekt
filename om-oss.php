<?php
$page_title       = 'Om oss – Lokala hantverkare i Göteborg';
$page_description = 'M2 Bygg Team AB – Lokalt förankrat byggföretag i Hisings Backa. Fast pris, 1 000+ nöjda kunder. Läs om oss.';
$active_page      = 'om-oss';
$breadcrumbs      = [['Hem', '/'], ['Om oss', null]];
require_once __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Om oss</span>
</div></div></div>

<!-- HERO -->
<section class="hero" style="min-height:auto;padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Hisings Backa, Göteborg</p>
      <h1 class="animate-in delay-1" style="margin-bottom:16px">Lokalt förankrade.<br>Genuint engagerade.</h1>
      <p class="animate-in delay-2">M2 Bygg Team AB är ett lokalt byggföretag i Hisings Backa. Vi arbetar varje dag för att ge dig den tryggaste upplevelsen av att renovera ditt hem – till ett fast pris du kan lita på.</p>
    </div>
  </div>
</section>

<!-- STATS -->
<div style="background:var(--copper)">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr)">
      <?php foreach([['1 000','+','Nöjda kunder'],['4,9','/5','Google-betyg'],['5',' år','Garanti'],['24','h','Svarstid']] as $i=>$s): ?>
      <div style="padding:24px;text-align:center;border-right:1px solid rgba(255,255,255,.2);<?= $i===3?'border-right:none':'' ?>">
        <div style="font-family:var(--font-display);font-size:2.4rem;font-weight:700;letter-spacing:-0.04em;color:#fff;line-height:1">
          <?= $s[0] ?><span style="color:rgba(255,255,255,.7)"><?= $s[1] ?></span>
        </div>
        <div style="font-size:13px;color:rgba(255,255,255,.75);margin-top:5px"><?= $s[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- STORY -->
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:start">
      <div>
        <div style="position:relative">
          <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=900&q=80"
               alt="M2 Bygg Team hantverkare"
               loading="lazy"
               style="width:100%;height:500px;object-fit:cover;border-radius:var(--r-xl);box-shadow:var(--shadow-xl)">
          <div style="position:absolute;bottom:20px;left:20px;background:rgba(29,29,31,.88);backdrop-filter:blur(12px);border-radius:var(--r-lg);padding:14px 18px;display:flex;align-items:center;gap:11px;border:1px solid rgba(255,255,255,.1)">
            <div style="width:40px;height:40px;background:rgba(181,113,42,.2);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--copper-lt)"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div style="font-family:var(--font-display);font-weight:600;font-size:14px;color:#fff">Lillhagsvägen 88</div>
              <div style="font-size:12px;color:rgba(255,255,255,.45)">442 43 Hisings Backa, Göteborg</div>
            </div>
          </div>
        </div>
      </div>
      <div class="reveal">
        <p class="eyebrow" style="margin-bottom:16px">Vår historia</p>
        <h2 style="margin-bottom:20px">Göteborg är<br>vår hemstad</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:16px">M2 Bygg Team AB grundades med en enkel idé: att erbjuda professionella byggtjänster med ett fast pris som kunden kan lita på – utan överraskningar, utan dolda avgifter.</p>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:16px">Vi är ett lokalt förankrat företag i Hisings Backa som arbetar med tak, fasad, plåt och mark i hela Göteborg och Västsverige. Vår styrka är kombinationen av hantverksskicklighet, lokal kunskap och tydlig kommunikation.</p>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:32px">Varje projekt, litet som stort, behandlas med samma omsorg. Det är det som gör att 1 000+ kunder valt att rekommendera oss.</p>

        <div style="display:flex;flex-direction:column;gap:16px" class="reveal-group">
          <?php
          $milestones = [
            ['icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','h'=>'Fast pris – alltid','p'=>'Du vet priset innan vi börjar. Prisgaranti ingår i varje offert.'],
            ['icon'=>'<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','h'=>'Nöjd kund-garanti','p'=>'Uppstår problem till följd av vårt arbete åtgärdar vi det kostnadsfritt.'],
            ['icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','h'=>'1 000+ nöjda kunder','p'=>'Mer än 1 000 kunder i Göteborg och Västsverige – och de flesta rekommenderar oss vidare.'],
          ];
          foreach ($milestones as $m): ?>
          <div class="reveal" style="display:flex;gap:14px;align-items:flex-start">
            <div style="width:40px;height:40px;background:rgba(181,113,42,.1);border:1px solid rgba(181,113,42,.2);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--copper)"><?= $m['icon'] ?></svg>
            </div>
            <div>
              <h4 style="margin-bottom:4px"><?= $m['h'] ?></h4>
              <p style="font-size:14px;color:var(--steel);line-height:1.6"><?= $m['p'] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- VALUES -->
<section class="section" style="background:var(--sand)">
  <div class="container">
    <div class="section-header reveal" style="text-align:center;align-items:center">
      <p class="eyebrow" style="margin:0 auto 14px">Våra värderingar</p>
      <h2 style="margin-bottom:10px">Det vi tror på varje dag</h2>
      <p style="margin:0 auto">Inte vad vi säger om oss själva – utan hur vi agerar mot varje kund.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:40px" class="reveal-group">
      <?php
      $values = [
        ['icon'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>','h'=>'Ärlighet i varje möte','p'=>'Vi lovar aldrig mer än vi kan hålla. Det pris vi sätter är det du betalar – varje gång, utan undantag.'],
        ['icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','h'=>'Trygghet som standard','p'=>'Varje projekt är försäkrat. Varje hantverkare är certifierad. Varje jobb garanteras i 5 år.'],
        ['icon'=>'<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','h'=>'Nöjd kund är inte nog','p'=>'Vi siktar på förvånad kund. Det finns alltid ett sätt att leverera lite mer, lite bättre.'],
        ['icon'=>'<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>','h'=>'Kommunikation hela vägen','p'=>'Du ska aldrig behöva undra vad som händer. Vi håller dig uppdaterad från offert till klart.'],
        ['icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','h'=>'Lokal gemenskap','p'=>'Vi är ett Göteborg-företag. Vi betalar skatt här, anställer härifrån och bryr oss om grannskapet.'],
        ['icon'=>'<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','h'=>'Ständig förbättring','p'=>'Vi lär oss av varje projekt. Nya material, bättre tekniker – vi håller oss alltid i framkant.'],
      ];
      foreach ($values as $v): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:var(--s8);transition:box-shadow .3s" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow='none'">
        <div style="width:48px;height:48px;background:var(--sand-lt);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;margin-bottom:var(--s5)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--copper)"><?= $v['icon'] ?></svg>
        </div>
        <h3 style="font-size:1rem;margin-bottom:7px"><?= $v['h'] ?></h3>
        <p style="font-size:14px;color:var(--steel);line-height:1.62"><?= $v['p'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ADDRESS / CONTACT -->
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start">
      <div class="reveal">
        <p class="eyebrow" style="margin-bottom:16px">Hitta oss</p>
        <h2 style="margin-bottom:8px">Besök oss i<br>Hisings Backa</h2>
        <p style="color:var(--steel);margin-bottom:28px">Vi finns i Hisings Backa och täcker hela Göteborg och Västsverige.</p>
        <div style="display:flex;flex-direction:column;gap:12px">
          <?php
          $rows = [
            ['<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>','Lillhagsvägen 88','442 43 Hisings Backa','https://maps.google.com/?q=Lillhagsvagen+88'],
            ['<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/>','031-96 88 88  ·  0732-40 50 26','Mån–Fre 07:00–18:00','tel:031968888'],
            ['<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>','info@m2team.se','Svar inom 24 timmar','mailto:info@m2team.se'],
          ];
          foreach ($rows as $r): ?>
          <a href="<?= $r[3] ?>" <?= str_starts_with($r[3],'http')?'target="_blank" rel="noopener"':'' ?> style="display:flex;align-items:flex-start;gap:12px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:16px 18px;transition:all .2s;color:inherit" onmouseover="this.style.borderColor='var(--copper)';this.style.background='var(--white)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--sand-lt)'">
            <div style="width:38px;height:38px;background:rgba(181,113,42,.1);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--copper)"><?= $r[0] ?></svg>
            </div>
            <div>
              <div style="font-family:var(--font-display);font-weight:600;font-size:14.5px;color:var(--coal);margin-bottom:3px"><?= $r[1] ?></div>
              <div style="font-size:12.5px;color:var(--steel-lt)"><?= $r[2] ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>

        <div style="margin-top:20px">
          <div style="font-size:11px;font-weight:600;color:var(--steel);letter-spacing:.09em;text-transform:uppercase;margin-bottom:10px">Följ oss</div>
          <div style="display:flex;gap:10px">
            <a href="<?= $instagram ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:9px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:12px 16px;transition:all .2s;font-size:14px;color:var(--coal)" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px;color:var(--copper)"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
              @m2byggteam
            </a>
            <a href="<?= $facebook ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:9px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:12px 16px;transition:all .2s;font-size:14px;color:var(--coal)" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px;color:var(--copper)"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
              M2 Bygg Team
            </a>
          </div>
        </div>
      </div>

      <!-- Certifications -->
      <div class="reveal">
        <p class="eyebrow" style="margin-bottom:16px">Certifieringar</p>
        <h2 style="margin-bottom:8px">Du kan lita på oss</h2>
        <p style="color:var(--steel);margin-bottom:28px">Alla certifieringar, garantier och försäkringar för din trygghet.</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="reveal-group">
          <?php
          $certs = [
            ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','Försäkrad verksamhet','Fullständig ansvarsförsäkring'],
            ['<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','Certifierade hantverkare','Yrkescertifierade inom alla specialområden'],
            ['<path d="M9 14l6-6M9.5 8.5a.5.5 0 110-1 .5.5 0 010 1zm5 5a.5.5 0 110-1 .5.5 0 010 1z"/><rect x="3" y="3" width="18" height="18" rx="3"/>','ROT-godkänd','Registrerade hos Skatteverket'],
            ['<path d="M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3"/>','Kostnadsfri offert','Svar inom 24h'],
          ];
          foreach ($certs as $c): ?>
          <div class="reveal" style="background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:20px;text-align:center">
            <div style="width:48px;height:48px;background:rgba(181,113,42,.1);border:1px solid rgba(181,113,42,.18);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--copper)"><?= $c[0] ?></svg>
            </div>
            <div style="font-family:var(--font-display);font-weight:600;font-size:14px;color:var(--coal);margin-bottom:4px"><?= $c[1] ?></div>
            <div style="font-size:12.5px;color:var(--steel)"><?= $c[2] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap">
      <div class="reveal">
        <p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Kom igång</p>
        <h2 style="margin-bottom:8px">Redo att komma igång?</h2>
        <p>Gratis besiktning och offert inom 24 timmar. Inga förpliktelser.</p>
      </div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-white btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          031-96 88 88
        </a>
        <a href="/offert" class="btn btn--copper btn--lg">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
          Begär gratis offert
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
