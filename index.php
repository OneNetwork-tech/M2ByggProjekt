<?php
$page_title       = 'Trygga byggtjänster till fast pris | M2 Bygg Team';
$page_description = 'M2 Bygg Team AB – Professionell takrenovering, fasadmålning och plåtarbeten i Göteborg. Fast pris, 5 år garanti, ROT-avdrag. Ring 031-96 88 88.';
$active_page      = 'hem';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══ HERO — full-bleed house photo, asymmetric overlay, word-by-word headline ═══ -->
<section class="hero" id="hero">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D')"></div>
  <div class="hero__overlay"></div>
  <div class="hero__bottom-fade"></div>

  <div class="container hero__content">

    <!-- Headline: each word wrapped for clip-reveal animation -->
    <div class="hero__headline">
      <h1 id="hero-h1">
        <?php
        // Word-by-word structure matching reference exactly
        $lines = [
          ['Trygga'],
          ['byggtjänster'],
          ['till', '<em>fast pris.</em>'],
        ];
        foreach ($lines as $words) {
          foreach ($words as $word) {
            $isHtml = strpos($word, '<') !== false;
            echo '<span class="hero__word"><span class="hero__word-inner">';
            echo $isHtml ? $word : htmlspecialchars($word);
            echo '</span></span>';
          }
          echo '<br>';
        }
        ?>
      </h1>
    </div>

    <p class="hero__sub">
      Kvalitetssäkrade partners. Inga överraskningar.<br>Nöjd kund garanti.
    </p>

    <div class="hero__actions">
      <a href="/offert" class="btn btn--primary btn--lg">Få kostnadsfri offert</a>
      <a href="/projekt" class="btn btn--outline-dark btn--lg">Visualisera ditt hus</a>
    </div>

    <!-- Trust badges — exactly like reference -->
    <div class="hero__trust">
      <?php foreach([
        ['<path d="M9 14l6-6M9.5 8.5a.5.5 0 110-1 .5.5 0 010 1zm5 5a.5.5 0 110-1 .5.5 0 010 1z"/><rect x="3" y="3" width="18" height="18" rx="3"/>','Fast pris','Inga överraskningar'],
        ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','Kvalitetssäkrade partners',''],
        ['<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','Nöjd kund garanti','Vi lämnar dig inte förrän du är nöjd'],
      ] as $t): ?>
      <div class="hero__trust-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $t[0] ?></svg>
        <div>
          <strong><?= $t[1] ?></strong>
          <?php if ($t[2]): ?><span><?= $t[2] ?></span><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ═══ SERVICES — photo cards exactly like reference ═══ -->
<section class="section" style="background:var(--white);padding-top:80px">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:48px">
      <span class="eyebrow">Våra tjänster</span>
      <h2 style="font-size:clamp(1.7rem,3vw,2.6rem)">Allt för ditt hus – utvändigt<br>och invändigt.</h2>
    </div>

    <!-- 4-col photo card grid matching reference -->
    <div class="services-photo-grid reveal-group">
      <?php
      $main_services = [
        ['cat'=>'TAK',          'desc'=>'Målning, tvätt, byte och reparation.','href'=>'/tjanster/takbyte',
         'img'=>'/uploads/tak-takmalning.jpg',
         'icon'=>'<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['cat'=>'FASAD',        'desc'=>'Tvätt, målning och fasadrenovering.','href'=>'/tjanster/fasadmalning',
         'img'=>'/uploads/fasad-renovering.jpg',
         'icon'=>'<rect x="2" y="3" width="20" height="14" rx="2"/>'],
        ['cat'=>'PLÅT & DETALJER','desc'=>'Hängrännor, stuprör, plåtarbeten och beslag.','href'=>'/tjanster/platarbeten',
         'img'=>'/uploads/tak-takbyte-1.jpg',
         'icon'=>'<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
        ['cat'=>'STEN & MARK',  'desc'=>'Markarbete, stenläggning, trappor och kantsten.','href'=>'/tjanster/stenlaggning',
         'img'=>'/uploads/mark-stenlaggning.jpg',
         'icon'=>'<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>'],
      ];
      foreach ($main_services as $s): ?>
      <a href="<?= e($s['href']) ?>" class="service-photo-card reveal" style="text-decoration:none">
        <img class="service-photo-card__img"
             src="<?= e($s['img']) ?>"
             alt="<?= e($s['cat']) ?>"
             loading="lazy">
        <div class="service-photo-card__icon-wrap">
          <div class="service-photo-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $s['icon'] ?></svg>
          </div>
        </div>
        <div class="service-photo-card__body">
          <div class="service-photo-card__title"><?= e($s['cat']) ?></div>
          <p class="service-photo-card__desc"><?= e($s['desc']) ?></p>
          <span class="service-photo-card__link">
            Läs mer
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="reveal" style="text-align:center;margin-top:44px">
      <a href="/tjanster" class="btn btn--dark btn--lg">Se alla tjänster</a>
    </div>
  </div>
</section>

<!-- ═══ BEFORE / AFTER SLIDER — "Visualisera ditt hem" ═══ -->
<section class="section" style="background:var(--surface)">
  <div class="container">
    <div class="ba-section">
      <div>
        <span class="eyebrow reveal">Visualisera ditt hem</span>
        <h2 class="reveal" style="margin:8px 0 16px;line-height:1.1">Se skillnaden.<br>Innan du bestämmer dig.</h2>
        <p class="reveal" style="margin-bottom:28px;max-width:360px">Ladda upp en bild på ditt hus så visar vi hur det kan se ut med olika färger och material.</p>
        <a href="/offert" class="btn btn--dark reveal">Prova visualisering</a>
      </div>

      <!-- Before / after interactive slider -->
      <div class="ba-wrap reveal">
        <span class="ba-label ba-label--before">Före</span>
        <span class="ba-label ba-label--after">Efter</span>
        <!-- BEFORE image -->
        <img src="/uploads/takmalning-1.png"
             alt="Tak före takmålning" loading="lazy">
        <!-- AFTER image (clips from handle) -->
        <div class="ba-after" style="clip-path:inset(0 0 0 50%)">
          <img src="/uploads/takmalning-2.png"
               alt="Tak efter takmålning" loading="lazy">
        </div>
        <div class="ba-handle" style="left:50%"></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ HOW IT WORKS — "Så fungerar det" ═══ -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:56px">
      <span class="eyebrow">Så fungerar det</span>
      <h2>Enkelt, tryggt och smidigt.</h2>
    </div>

    <div class="steps-row reveal-group">
      <?php
      $steps = [
        ['1. Skicka bilder','Ladda upp bilder på ditt hus och beskriv ditt projekt.','<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>'],
        ['2. Få fast pris','Vi återkommer snabbt med förslag och fast pris.','<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
        ['3. Vi utför arbetet','Våra kvalitetssäkrade partners utför arbetet – du njuter av resultatet.','<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
      ];
      foreach ($steps as $i => $step): ?>
      <div class="step reveal">
        <div class="step__icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><?= $step[2] ?></svg>
        </div>
        <div>
          <div class="step__label"><?= $step[0] ?></div>
          <p class="step__desc"><?= $step[1] ?></p>
        </div>
      </div>
      <?php if ($i < 2): ?>
      <div class="step-arrow" style="padding-top:18px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </div>
      <?php endif; endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ DARK STATS BAR — exactly like reference bottom bar ═══ -->
<div class="stats-bar">
  <div class="container">
    <div class="stats-bar__grid">
      <?php foreach([
        ['<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','1000','+',' Nöjda kunder'],
        ['<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>','50','+',' Kvalitetssäkrade partners'],
        ['<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','4.9','/5',' Snittbetyg på rekommendationer'],
        ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','','','Nöjd kund garanti – vi lämnar dig inte förrän du är helt nöjd'],
      ] as $s): ?>
      <div class="stat">
        <div class="stat__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><?= $s[0] ?></svg>
        </div>
        <div>
          <?php if ($s[1]): ?>
          <div class="stat__num">
            <span class="counter__num" data-target="<?= $s[1] ?>" data-suffix="<?= htmlspecialchars($s[2]) ?>"><?= $s[1] ?><?= $s[2] ?></span>
          </div>
          <?php endif; ?>
          <div class="stat__label"><?= ltrim($s[3]) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ═══ COMPLETED PROJECTS ═══ -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:44px">
      <span class="eyebrow">Utförda projekt</span>
      <h2>Se vad vi har gjort</h2>
      <p style="color:var(--steel);max-width:520px;margin:12px auto 0">Riktiga projekt utförda av M2 Bygg Team i Göteborg och Västsverige.</p>
    </div>
    <?php
    $projects = [
      ['img'=>'/uploads/tak-takmalning.jpg',      'label'=>'Takmålning',      'href'=>'/tjanster/takmalning',      'featured'=>true],
      ['img'=>'/uploads/fasad-renovering.jpg',     'label'=>'Fasadrenovering', 'href'=>'/tjanster/fasadrenovering', 'featured'=>false],
      ['img'=>'/uploads/fasad-balkong.jpg',        'label'=>'Balkongmålning',  'href'=>'/tjanster/balkongmalning',  'featured'=>false],
      ['img'=>'/uploads/fasad-kladinfasad-1.jpg',  'label'=>'Klä in fasad',    'href'=>'/tjanster/kladinfasad',     'featured'=>false],
      ['img'=>'/uploads/tak-takbyte-1.jpg',        'label'=>'Takbyte',         'href'=>'/tjanster/takbyte',         'featured'=>false],
      ['img'=>'/uploads/tak-takbyte-2.jpg',        'label'=>'Plåtarbeten',     'href'=>'/tjanster/platarbeten',     'featured'=>false],
      ['img'=>'/uploads/fasad-kladinfasad-2.jpg',  'label'=>'Klä in fasad',    'href'=>'/tjanster/kladinfasad',     'featured'=>false],
      ['img'=>'/uploads/fasad-kladinfasad-3.jpg',  'label'=>'Klä in fasad',    'href'=>'/tjanster/kladinfasad',     'featured'=>false],
      ['img'=>'/uploads/mark-stenlaggning.jpg',    'label'=>'Stenläggning',    'href'=>'/tjanster/stenlaggning',    'featured'=>false],
    ];
    ?>
    <div class="projects-grid reveal-group">
      <!-- Featured TAK card: before/after slider -->
      <a href="/tjanster/takmalning" class="project-card project-card--featured project-card--ba reveal" aria-label="Takmålning – före och efter">
        <div class="pc-ba-wrap" data-pc-ba>
          <span class="pc-ba-label pc-ba-label--before">Före</span>
          <span class="pc-ba-label pc-ba-label--after">Efter</span>
          <img src="/uploads/takmalning-1.png" alt="Tak före takmålning" loading="lazy" class="pc-ba-img pc-ba-img--before">
          <div class="pc-ba-after">
            <img src="/uploads/takmalning-2.png" alt="Tak efter takmålning" loading="lazy" class="pc-ba-img">
          </div>
          <div class="pc-ba-handle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path d="M8 5l-5 7 5 7M16 5l5 7-5 7"/></svg>
          </div>
        </div>
        <div class="project-card__overlay project-card__overlay--ba">
          <span class="project-card__label">Takmålning</span>
          <span class="project-card__cta">
            Se tjänst
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </span>
        </div>
      </a>
      <?php foreach (array_slice($projects, 1) as $p): ?>
      <a href="<?= e($p['href']) ?>" class="project-card reveal">
        <img src="<?= e($p['img']) ?>" alt="<?= e($p['label']) ?> – M2 Bygg Team" loading="lazy">
        <div class="project-card__overlay">
          <span class="project-card__label"><?= e($p['label']) ?></span>
          <span class="project-card__cta">
            Se tjänst
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="reveal" style="text-align:center;margin-top:36px">
      <a href="/tjanster" class="btn btn--outline-light btn--lg">Se alla tjänster</a>
    </div>
  </div>
</section>

<!-- ═══ TESTIMONIALS ═══ -->
<section class="section" style="background:var(--surface)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:44px">
      <span class="eyebrow">Kundrecensioner</span>
      <h2>Vad kunderna säger</h2>
    </div>
    <div class="testimonials-grid reveal-group">
      <?php foreach([
        [5,'Fantastiskt takbyte! Fast pris som hölls, snabbt och noga. ROT-avdraget sköttes smidigt. Kan verkligen rekommendera – anlita ingen annan!','Johan Pettersson','Hovås, Göteborg','Jan 2025'],
        [5,'Fasadmålning av vår villa i Askim – otrolig förvandling. Kommunikationen var tydlig hela vägen. 5 stjärnor utan tvekan!','Maria Lindqvist','Askim, Göteborg','Dec 2024'],
        [5,'Anlitade M2 för taktvätt och takmålning. Professionellt team, snabb respons och exakt fast pris. Rekommenderas starkt.','Erik Karlsson','Kungsbacka','Feb 2025'],
      ] as $r): ?>
      <div class="review-card reveal">
        <div class="review-card__stars" aria-label="<?= $r[0] ?> av 5 stjärnor" role="img"><?= str_repeat('★', $r[0]) ?></div>
        <p class="review-card__text">"<?= htmlspecialchars($r[1]) ?>"</p>
        <div class="review-card__author">
          <div class="review-card__avatar"><?= implode('', array_map(fn($w)=>$w[0], explode(' ', $r[2]))) ?></div>
          <div>
            <div class="review-card__name"><?= htmlspecialchars($r[2]) ?></div>
            <div class="review-card__loc"><?= htmlspecialchars($r[3]) ?> · <?= $r[4] ?></div>
          </div>
          <div class="review-card__g">G</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ CTA BAND ═══ -->
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap">
      <div class="reveal">
        <span class="eyebrow">Kom igång idag</span>
        <h2 style="margin:10px 0 10px;color:var(--white)">Redo för ett fast pris?</h2>
        <p>Gratis besiktning och offert inom 24 timmar. Inga förpliktelser.</p>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>