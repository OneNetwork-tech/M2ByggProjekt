<?php
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/crm/includes/db.php';
$page_title       = 'M2 Bygg Team – Plattformen för bygg- och fastighetstjänster i Göteborg';
$page_description = 'M2 är mer än ett byggbolag – en modern plattform som samordnar kvalitetssäkrade partners för tak, fasad, mark och mer. En kontaktpunkt. Tryggt och transparent. Göteborg & Västsverige.';
$active_page      = 'hem';
$breadcrumbs      = null; // homepage has no breadcrumb trail
$lcp_image        = 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
require_once __DIR__ . '/includes/header.php';

$siteReviews = db()->query("
    SELECT r.*, c.name AS customer_name, c.city AS customer_city
    FROM reviews r JOIN customers c ON c.id = r.customer_id
    WHERE r.visible = 1
    ORDER BY r.created_at DESC LIMIT 6
")->fetchAll();
if (!function_exists('review_month_label')) {
    function review_month_label(string $dt): string {
        $months = ['jan','feb','mar','apr','maj','jun','jul','aug','sep','okt','nov','dec'];
        $ts = strtotime($dt);
        return $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
}
?>

<!-- ═══ HERO — full-bleed house photo, asymmetric overlay, word-by-word headline ═══ -->
<section class="hero" id="hero">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D')"></div>
  <div class="hero__overlay"></div>
  <div class="hero__bottom-fade"></div>

  <div style="position:absolute;top:24px;right:24px;z-index:5;font-size:.8rem">
    <?php
    $qs = $_GET; unset($qs['lang']);
    $svActive = current_lang() === 'sv' ? ' style="font-weight:700;text-decoration:underline"' : '';
    $enActive = current_lang() === 'en' ? ' style="font-weight:700;text-decoration:underline"' : '';
    ?>
    <a href="?<?= htmlspecialchars(http_build_query(array_merge($qs, ['lang' => 'sv']))) ?>" style="color:rgba(255,255,255,.7)"<?= $svActive ?>>SV</a>
    <span style="color:rgba(255,255,255,.4)"> / </span>
    <a href="?<?= htmlspecialchars(http_build_query(array_merge($qs, ['lang' => 'en']))) ?>" style="color:rgba(255,255,255,.7)"<?= $enActive ?>>EN</a>
  </div>

  <div class="container hero__content">

    <!-- Headline: each word wrapped for clip-reveal animation -->
    <div class="hero__headline">
      <h1 id="hero-h1">
        <?php
        $lines = current_lang() === 'en'
          ? [['A', 'modern'], ['platform'], ['for', '<em>construction.</em>']]
          : [['En', 'modern'], ['plattform'], ['för', '<em>byggtjänster.</em>']];
        foreach ($lines as $words) {
          foreach ($words as $word) {
            $isHtml = strpos($word, '<') !== false;
            echo '<span class="hero__word"><span class="hero__word-inner">';
            echo $isHtml ? $word : htmlspecialchars($word);
            echo '</span></span> ';
          }
          echo '<br>';
        }
        ?>
      </h1>
    </div>

    <p class="hero__sub">
      <?= t('home.hero_sub') ?>
    </p>

    <div class="hero__actions">
      <a href="/offert" class="btn btn--primary btn--lg"><?= e(t('home.cta_start')) ?></a>
      <a href="#plattformen" class="btn btn--outline-dark btn--lg"><?= e(t('home.cta_how')) ?></a>
    </div>

    <!-- Trust badges -->
    <div class="hero__trust">
      <?php foreach([
        ['<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>', t('home.trust_contact'), t('home.trust_contact_sub')],
        ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>', t('home.trust_quality'), t('home.trust_quality_sub')],
        ['<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', t('home.trust_transparent'), t('home.trust_transparent_sub')],
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
      <span class="eyebrow">Vad vi koordinerar</span>
      <h2 style="font-size:clamp(1.7rem,3vw,2.6rem)">Allt utvändigt –<br>ett samtal räcker.</h2>
      <p style="color:var(--steel);max-width:480px;margin:14px auto 0">Oavsett om det är tak, fasad, mark eller klotter – du kontaktar M2 och vi ser till att rätt specialist utför jobbet.</p>
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

<!-- ═══ PLATFORM CONCEPT ═══ -->
<section class="section platform-section" id="plattformen" style="background:var(--coal)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:56px">
      <span class="eyebrow" style="color:var(--gold-lt)">Konceptet</span>
      <h2 style="color:var(--white);max-width:620px;margin:12px auto 0">M2 är inte ett byggbolag.<br>Vi är en plattform.</h2>
      <p style="color:rgba(245,245,247,.65);max-width:540px;margin:16px auto 0;line-height:1.75">Det finns tusentals byggföretag. M2 är något annat – ett modernt system som kopplar ihop kunder och fastighetsägare med noggrant utvalda specialister, medan M2 håller ihop helheten.</p>
    </div>

    <div class="platform-pillars reveal-group">
      <?php foreach([
        ['<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
         'Du','Kunden',
         'Du kontaktar M2 med ditt projekt – via webb, telefon eller e-post. Det är allt du behöver göra.'],
        ['<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>',
         'M2','Plattformen',
         'M2 koordinerar, kvalitetssäkrar, projektleder och kommunicerar. Vi är din enda kontaktpunkt genom hela projektet.'],
        ['<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
         'Partners','Specialisterna',
         'Noggrant utvalda specialistföretag utför arbetet. M2 godkänner varje steg och garanterar resultatet.'],
      ] as [$icon,$tag,$title,$desc]): ?>
      <div class="platform-pillar reveal">
        <div class="platform-pillar__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" width="26" height="26"><?= $icon ?></svg>
        </div>
        <span class="platform-pillar__tag"><?= htmlspecialchars($tag) ?></span>
        <h3 class="platform-pillar__title"><?= htmlspecialchars($title) ?></h3>
        <p class="platform-pillar__desc"><?= htmlspecialchars($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Flow arrows -->
    <div class="platform-flow reveal">
      <div class="platform-flow__line"></div>
      <?php foreach(['Kontaktar M2','M2 koordinerar','Specialist levererar','Du godkänner'] as $i => $label): ?>
      <div class="platform-flow__step">
        <div class="platform-flow__dot"><span><?= $i+1 ?></span></div>
        <span class="platform-flow__label"><?= htmlspecialchars($label) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ HOW IT WORKS — "Så fungerar det" ═══ -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:56px">
      <span class="eyebrow">Så fungerar det</span>
      <h2>Fyra steg – från idé till färdigt projekt.</h2>
    </div>

    <div class="steps-row reveal-group">
      <?php
      $steps = [
        ['Kontakta M2','Berätta om ditt projekt via webb, telefon eller e-post. Inga ritningar krävs – vi hjälper dig med allt.','<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/>'],
        ['Fast pris & tidplan','M2 matchar rätt partner och återkommer med ett tydligt fast pris och en konkret tidplan. Inga dolda kostnader.','<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
        ['M2 projektleder','Specialisten utför arbetet. M2 håller koll på kvalitet, tidplan och kommunikation – du behöver inte göra ett endaste samtal till leverantören.','<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>'],
        ['Du godkänner','Projektet är klart. Du granskar och godkänner. Inte nöjd? Vi åtgärdar – utan diskussion. Det är vår garanti.','<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
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
      <?php if ($i < 3): ?>
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

<!-- ═══ PARTNER NETWORK ═══ -->
<section class="section" style="background:var(--surface)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:48px">
      <span class="eyebrow">Partnernätverk</span>
      <h2>Noggrant utvalda specialister</h2>
      <p style="color:var(--steel);max-width:520px;margin:14px auto 0">M2 samarbetar enbart med partners som uppfyller våra krav på kvalitet, försäkring, certifiering och kundupplevelse.</p>
    </div>
    <div class="partner-grid reveal-group">
      <?php foreach([
        ['Takspecialister','Certifierade takläggare och plåtslagare med dokumenterad erfarenhet av alla taktyper.','<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['Fasadentreprenörer','Målare och fasadrenovatörer med bred kompetens inom puts, trä, plåt och fibercementkivor.','<rect x="2" y="3" width="20" height="14" rx="2"/>'],
        ['Markentreprenörer','Specialist på stenläggning, markarbete, dränering och utemiljö.','<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>'],
        ['Klottersanerare','Snabb respons och dokumenterade saneringsmetoder. Tillgängliga dygnet runt för akuta ärenden.','<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
        ['Plåtslagare','Hängrännor, stuprör, beslag och detaljer. Förebygger vattenskador och förlänger byggnadens livslängd.','<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
        ['Bli partner','Är du ett kvalificerat företag inom bygg och fastighet? Vi söker alltid nya partners till nätverket.',null,'cta'],
      ] as $p): ?>
      <?php if (($p[3] ?? '') === 'cta'): ?>
      <a href="/bli-partner" class="partner-card partner-card--cta reveal">
        <div class="partner-card__icon partner-card__icon--cta">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="24" height="24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <h4><?= htmlspecialchars($p[0]) ?></h4>
        <p><?= htmlspecialchars($p[1]) ?></p>
        <span class="partner-card__cta-link">Ansök som partner →</span>
      </a>
      <?php else: ?>
      <div class="partner-card reveal">
        <div class="partner-card__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><?= $p[2] ?></svg>
        </div>
        <h4><?= htmlspecialchars($p[0]) ?></h4>
        <p><?= htmlspecialchars($p[1]) ?></p>
        <div class="partner-card__badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          M2-godkänd
        </div>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

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

<!-- ═══ B2B / FASTIGHET ═══ -->
<section class="section fastighet-section">
  <div class="fastighet-section__bg" aria-hidden="true"></div>
  <div class="container" style="position:relative;z-index:1">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center" class="fastighet-grid">

      <!-- Left: text -->
      <div>
        <span class="eyebrow reveal" style="color:var(--gold-lt)">Fastighetspartner</span>
        <h2 class="reveal" style="color:var(--white);margin:14px 0 18px">För fastighetsägare &amp; BRF</h2>
        <p class="reveal" style="color:rgba(245,245,247,.75);line-height:1.75;margin-bottom:28px">
          Vi bygger långsiktiga samarbeten med BRF:er, fastighetsbolag, fastighetsägare och förvaltare i Göteborg och Västsverige. Med löpande underhållsavtal och prioriterad service håller vi era fastigheter i toppskick – året runt.
        </p>

        <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:32px" class="reveal-group">
          <?php foreach([
            ['Ramavtal & löpande underhåll','Fast pris per säsong eller löpande räkning – anpassat efter er fastighetsportfölj.'],
            ['Prioriterad bokning','Som avtalspartner går ni alltid före i kön. Vi garanterar utförande inom överenskommen tid.'],
            ['En kontaktperson','Dedikerad projektledare för alla era fastigheter. Enkel kommunikation, tydlig rapportering.'],
            ['ROT & dokumentation','Vi sköter ROT-avdrag och levererar komplett dokumentation för era bokförare.'],
          ] as [$h,$p]): ?>
          <div class="reveal" style="display:flex;gap:14px;align-items:flex-start">
            <div style="min-width:36px;height:36px;background:rgba(201,168,76,.18);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">
              <svg viewBox="0 0 24 24" fill="none" stroke="var(--gold-lt)" stroke-width="2.2" width="17" height="17"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <strong style="display:block;color:var(--white);font-size:.93rem;margin-bottom:3px"><?= htmlspecialchars($h) ?></strong>
              <span style="font-size:.83rem;color:rgba(245,245,247,.6);line-height:1.6"><?= htmlspecialchars($p) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap">
          <a href="/fastighet" class="btn btn--primary btn--lg">Läs mer om fastighetsavtal</a>
          <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
        </div>
      </div>

      <!-- Right: customer type cards -->
      <div class="reveal-group">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <?php foreach([
            ['BRF','Bostadsrättsförening','M 4 12h16M4 6h16M4 18h16'],
            ['Fastighetsbolag','Kommersiella fastigheter','M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z'],
            ['Fastighetsägare','Privata hyresfastigheter','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
            ['Förvaltare','Teknisk förvaltning','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
          ] as [$title,$sub,$icon]): ?>
          <div class="fastighet-card reveal">
            <div class="fastighet-card__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="<?= $icon ?>"/></svg>
            </div>
            <strong class="fastighet-card__title"><?= htmlspecialchars($title) ?></strong>
            <span class="fastighet-card__sub"><?= htmlspecialchars($sub) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="fastighet-stats reveal">
          <?php foreach([['50+','Fastigheter underhållna'],['4,9/5','Nöjda kunder'],['5 år','Garanti på allt arbete']] as [$n,$l]): ?>
          <div class="fastighet-stat">
            <span class="fastighet-stat__num"><?= htmlspecialchars($n) ?></span>
            <span class="fastighet-stat__lbl"><?= htmlspecialchars($l) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<?php if ($siteReviews): ?>
<!-- ═══ TESTIMONIALS ═══ -->
<section class="section" style="background:var(--surface)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:44px">
      <span class="eyebrow">Kundrecensioner</span>
      <h2>Vad kunderna säger</h2>
    </div>
    <div class="testimonials-grid reveal-group">
      <?php foreach ($siteReviews as $r): ?>
      <div class="review-card reveal">
        <div class="review-card__stars" aria-label="<?= (int)$r['rating'] ?> av 5 stjärnor" role="img"><?= str_repeat('★', (int)$r['rating']) ?></div>
        <p class="review-card__text">"<?= e($r['body']) ?>"</p>
        <div class="review-card__author">
          <div class="review-card__avatar"><?= e(implode('', array_map(fn($w)=>mb_substr($w,0,1), explode(' ', $r['customer_name'])))) ?></div>
          <div>
            <div class="review-card__name"><?= e($r['customer_name']) ?></div>
            <div class="review-card__loc"><?= e($r['customer_city'] ?: '') ?> · <?= e(review_month_label($r['created_at'])) ?></div>
          </div>
          <div class="review-card__g">G</div>
        </div>
        <?php if ($r['reply_body']): ?>
        <div style="margin-top:14px;padding:12px 14px;background:var(--surface);border-radius:var(--r-md);border-left:3px solid var(--copper)">
          <div style="font-size:11.5px;font-weight:600;color:var(--copper);margin-bottom:4px">Svar från M2 Bygg Team</div>
          <div style="font-size:13px;color:var(--steel)"><?= e($r['reply_body']) ?></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

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