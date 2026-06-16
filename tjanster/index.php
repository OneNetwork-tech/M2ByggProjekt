<?php
$page_title       = 'Alla Tjänster – Tak, Fasad, Balkong & Mark | M2 Bygg Team Göteborg';
$page_description = 'Alla tjänster från M2 Bygg Team AB – takbyte, takmålning, fasadmålning, klä in fasad, balkongmålning, plåtarbeten och stenläggning. Fast pris, ROT-avdrag, 5 år garanti i Göteborg och Västsverige.';
$active_page      = 'tjanster';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Tjänster</span>
</div></div></div>

<!-- HERO -->
<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content" style="text-align:center">
    <div style="max-width:640px;margin:0 auto">
      <p class="eyebrow animate-in" style="color:var(--gold-lt);display:block;margin-bottom:18px">Fast pris · ROT-avdrag · 5 år garanti</p>
      <h1 class="animate-in" style="margin-bottom:16px">Alla tjänster –<br><em>ett företag</em></h1>
      <p class="animate-in" style="color:rgba(255,255,255,.82);max-width:480px;margin:0 auto 28px">Tak, fasad, balkong, plåt och mark i Göteborg, Kungsbacka, Trollhättan, Varberg och hela Västsverige. Slipper du koordinera flera leverantörer.</p>
      <div class="animate-in" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="/offert" class="btn btn--primary btn--lg">Få kostnadsfri offert</a>
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
        ['<path d="M9 14l6-6M9.5 8.5a.5.5 0 110-1 .5.5 0 010 1zm5 5a.5.5 0 110-1 .5.5 0 010 1z"/><rect x="3" y="3" width="18" height="18" rx="3"/>','Fast pris','Prisgaranti alltid'],
        ['<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','ROT-avdrag','Vi hanterar ansökan'],
        ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','5 år garanti','På allt arbete'],
        ['<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','4,9/5 Betyg','1 000+ nöjda kunder'],
      ] as $t): ?>
      <div class="trust-item">
        <div class="trust-item__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $t[0] ?></svg>
        </div>
        <div class="trust-item__text"><strong><?= $t[1] ?></strong><span><?= $t[2] ?></span></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php
$categories = [

  /* ── TAK ─────────────────────────────────────────────── */
  [
    'id'    => 'tak',
    'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
    'title' => 'Takarbeten',
    'desc'  => 'Alla typer av takarbeten – takbyte, takrenovering, takmålning, taktvätt och plåtarbeten. Fast pris på alla material och taktyper.',
    'bg'    => '',
    'services' => [
      [
        'href'  => '/tjanster/takbyte',
        'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
        'title' => 'Takbyte',
        'desc'  => 'Komplett takbyte i alla material – tegeltak, betongtak, plåttak och papptak. Demontering och nytt underlag ingår.',
        'price' => 'från 900 kr/m²',
      ],
      [
        'href'  => '/tjanster/takrenovering',
        'icon'  => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
        'title' => 'Takrenovering',
        'desc'  => 'Partiell reparation – laga läckor, byta skadade pannor och åtgärda detaljer utan att byta hela taket.',
        'price' => 'Fast pris',
      ],
      [
        'href'  => '/tjanster/takmalning',
        'icon'  => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/>',
        'title' => 'Takmålning',
        'desc'  => 'Förnyar och skyddar taket 10–15 år till en bråkdel av kostnaden för ett nytt tak. Före & efter-resultat som syns direkt.',
        'price' => 'från 150 kr/m²',
      ],
      [
        'href'  => '/tjanster/taktvatt',
        'icon'  => '<path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>',
        'title' => 'Taktvätt',
        'desc'  => 'Högtryckstvätt och mossbehandling som förlänger takets livslängd med 5–10 år. Snabbt och effektivt.',
        'price' => 'från 80 kr/m²',
      ],
      [
        'href'  => '/tjanster/platarbeten',
        'icon'  => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
        'title' => 'Plåtarbeten',
        'desc'  => 'Hängrännor, stuprör, plåttak och beslag. Stål, zink, aluminium och koppar. Skyddar grunden mot fuktskador.',
        'price' => 'Fast pris',
      ],
    ],
  ],

  /* ── FASAD ───────────────────────────────────────────── */
  [
    'id'    => 'fasad',
    'icon'  => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
    'title' => 'Fasad & Balkong',
    'desc'  => 'Fasadmålning, fasadrenovering, infaskning och balkongmålning. Gratis färgkonsultation ingår alltid. Före & efter-foton på riktiga projekt.',
    'bg'    => 'background:var(--surface)',
    'services' => [
      [
        'href'  => '/tjanster/fasadmalning',
        'icon'  => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>',
        'title' => 'Fasadmålning',
        'desc'  => 'Förvandlar och skyddar fasaden i 10–15 år. Träfasad, puts, tegel och skivor med gratis färgkonsultation.',
        'price' => 'från 180 kr/m²',
      ],
      [
        'href'  => '/tjanster/fasadrenovering',
        'icon'  => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
        'title' => 'Fasadrenovering',
        'desc'  => 'Lagning av sprickor, skador och åldrande fasad. Komplett renovering som håller i 15–20 år.',
        'price' => 'Fast pris',
      ],
      [
        'href'  => '/tjanster/fasadtvatt',
        'icon'  => '<path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>',
        'title' => 'Fasadtvätt',
        'desc'  => 'Högtryckstvätt mot mögel, alger och smuts. Förlänger fasadens livslängd och förbered för målning.',
        'price' => 'från 100 kr/m²',
      ],
      [
        'href'  => '/tjanster/kladinfasad',
        'icon'  => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>',
        'title' => 'Klä in fasad',
        'desc'  => 'Träpanel, fibercementskivor eller puts. Komplett infaskning från rivning till färdig fasad – verkliga resultat som syns.',
        'price' => 'från 350 kr/m²',
      ],
      [
        'href'  => '/tjanster/balkongmalning',
        'icon'  => '<rect x="3" y="11" width="18" height="10" rx="1"/><path d="M3 11V7a2 2 0 012-2h14a2 2 0 012 2v4"/><line x1="12" y1="6" x2="12" y2="11"/>',
        'title' => 'Balkongmålning',
        'desc'  => 'Balkong, räcke, trappa och uteplats. Rostskyddsbehandling för metall och träolja/lackering för trä.',
        'price' => 'från 95 kr/lpm',
      ],
    ],
  ],

  /* ── MARK ────────────────────────────────────────────── */
  [
    'id'    => 'mark',
    'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>',
    'title' => 'Mark & Övrigt',
    'desc'  => 'Markarbete, stenläggning och klottersanering. Komplett service för utemiljön med fast pris.',
    'bg'    => '',
    'services' => [
      [
        'href'  => '/tjanster/markarbete',
        'icon'  => '<path d="M2 20h20M4 20V10l8-8 8 8v10"/>',
        'title' => 'Markarbete',
        'desc'  => 'Schaktning, dränering och markplanering med rätt maskiner. Förbereder för stenläggning och anläggning.',
        'price' => 'Fast pris',
      ],
      [
        'href'  => '/tjanster/stenlaggning',
        'icon'  => '<rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/>',
        'title' => 'Stenläggning',
        'desc'  => 'Terrass, uppfart och gångvägar i natursten och betongsten. Snyggt, hållbart och fastighetsvärdesökande.',
        'price' => 'från 500 kr/m²',
      ],
      [
        'href'  => '/tjanster/klottersanering',
        'icon'  => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/>',
        'title' => 'Klottersanering',
        'desc'  => 'Professionell borttagning av klotter på alla underlag. Skyddsbehandling ingår. Snabb respons.',
        'price' => 'Snabb respons',
      ],
    ],
  ],
];
?>

<?php foreach ($categories as $ci => $cat): ?>
<section id="<?= $cat['id'] ?>" class="section" style="<?= $cat['bg'] ?>">
  <div class="container">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:40px;flex-wrap:wrap">
      <div class="reveal">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
          <div style="width:48px;height:48px;background:var(--gold-lt);border:1px solid rgba(201,168,76,.25);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;color:var(--gold-dk)"><?= $cat['icon'] ?></svg>
          </div>
          <h2 style="font-size:clamp(1.6rem,3vw,2.2rem)"><?= $cat['title'] ?></h2>
        </div>
        <p style="color:var(--steel);max-width:520px;font-size:0.95rem"><?= $cat['desc'] ?></p>
      </div>
      <a href="/offert" class="btn btn--dark reveal" style="flex-shrink:0">Begär offert</a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px" class="reveal-group">
      <?php foreach ($cat['services'] as $s): ?>
      <a href="<?= e($s['href']) ?>" class="service-card reveal">
        <div class="service-card__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $s['icon'] ?></svg>
        </div>
        <span class="price-badge" style="margin-bottom:10px"><?= e($s['price']) ?></span>
        <h3 style="font-size:1rem;font-weight:600;color:var(--coal);text-transform:none;letter-spacing:0;margin-bottom:8px"><?= e($s['title']) ?></h3>
        <p style="font-size:0.85rem;color:var(--steel);line-height:1.65;flex:1;margin-bottom:18px"><?= e($s['desc']) ?></p>
        <span class="service-card__link">
          Läs mer
          <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
        </span>
      </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endforeach; ?>

<!-- SERVICE AREAS -->
<section class="section" style="background:var(--surface)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:40px">
      <span class="eyebrow">Våra verksamhetsområden</span>
      <h2>Vi jobbar i hela Västsverige</h2>
      <p style="max-width:480px;margin:12px auto 0">Fast pris och samma garanti oavsett var i regionen du bor. Ingen resekostnad inom Stor-Göteborg.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px" class="reveal-group">
      <?php foreach([
        ['Göteborg',      '/goteborg'],
        ['Hisingen',      '/hisingen'],
        ['Mölndal',       '/molndal'],
        ['Kungsbacka',    '/kungsbacka'],
        ['Kungälv',       '/kungalv'],
        ['Lerum',         '/lerum'],
        ['Mölnlycke',     '/molnlycke'],
        ['Trollhättan',   '/trollhattan'],
        ['Alingsås',      '/alingsas'],
        ['Åskim',         '/askim'],
        ['Landvetter',    '/kontakt'],
        ['Varberg',       '/kontakt'],
        ['Partille',      '/kontakt'],
        ['Uddevalla',     '/kontakt'],
        ['Halmstad',      '/kontakt'],
        ['Falkenberg',    '/kontakt'],
      ] as [$city, $href]): ?>
      <a href="<?= e($href) ?>" class="reveal" style="display:flex;align-items:center;gap:8px;padding:12px 16px;background:var(--white);border:1px solid var(--border);border-radius:var(--r-md);font-size:0.84rem;font-weight:500;color:var(--coal);transition:border-color .2s,box-shadow .2s;text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--gold);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <?= e($city) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal">
        <span class="eyebrow">Hittade du inte vad du söker?</span>
        <h2 style="margin:10px 0;color:var(--white)">Ring oss direkt</h2>
        <p>Vi utför fler tjänster än vad som visas här. Fast pris och 5 år garanti gäller alltid.</p>
      </div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          031-96 88 88
        </a>
        <a href="/offert" class="btn btn--primary btn--lg">Få kostnadsfri offert</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
