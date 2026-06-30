<?php
$page_title = 'Galleri – Tidigare Projekt';
$page_description = 'Se bilder från tidigare projekt utförda av M2 Bygg Team AB – fasadmålning, takläggning, fasadtvätt och renovering i Göteborg.';
$active_page = 'galleri';
$breadcrumbs = [['Hem', '/'], ['Galleri', null]];
require_once __DIR__ . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Galleri</span>
</div></div></div>
<section class="hero" style="padding:70px 0 56px;text-align:center;min-height:0">
  <div class="hero__bg" style="background-image:url('/uploads/gallery/fasadrenovering.avif')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <p class="eyebrow animate-in" style="color:var(--copper-lt);justify-content:center">Tidigare projekt</p>
    <h1 class="animate-in delay-1" style="margin-bottom:14px">Galleri</h1>
    <p class="animate-in delay-2" style="max-width:560px;margin:0 auto;color:rgba(245,245,247,.78)">Nedan kan du se bilder från några av de projekt vi har gjort tidigare.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px">
      <?php
      $pairs = [
        ['Fasadmålning', '/uploads/gallery/fasadmalning-fore.avif', '/uploads/gallery/fasadmalning-efter.avif'],
        ['Staket', '/uploads/gallery/staket-fore.avif', '/uploads/gallery/staket-efter.avif'],
        ['Takmålning', '/uploads/gallery/takmalning-fore.avif', '/uploads/gallery/takmalning-efter.avif'],
      ];
      foreach ($pairs as $p): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden">
        <div style="display:grid;grid-template-columns:1fr 1fr">
          <div style="position:relative">
            <img src="<?= e($p[1]) ?>" alt="<?= e($p[0]) ?> före" loading="lazy" style="width:100%;height:220px;object-fit:cover">
            <span style="position:absolute;top:10px;left:10px;background:rgba(17,19,24,.75);color:#fff;font-size:11px;font-weight:600;padding:4px 10px;border-radius:99px">Före</span>
          </div>
          <div style="position:relative">
            <img src="<?= e($p[2]) ?>" alt="<?= e($p[0]) ?> efter" loading="lazy" style="width:100%;height:220px;object-fit:cover">
            <span style="position:absolute;top:10px;left:10px;background:rgba(181,113,42,.9);color:#fff;font-size:11px;font-weight:600;padding:4px 10px;border-radius:99px">Efter</span>
          </div>
        </div>
        <p style="padding:14px 18px;font-weight:600;font-size:14.5px"><?= e($p[0]) ?></p>
      </div>
      <?php endforeach; ?>

      <?php
      $singles = [
        ['Träfasad efter målning', '/uploads/gallery/trafasad-malning.avif'],
        ['Träfasad efter tvätt', '/uploads/gallery/trafasad-tvatt.avif'],
        ['Fasadrenovering', '/uploads/gallery/fasadrenovering.avif'],
        ['Färdigställd takläggning', '/uploads/gallery/taklaggning-efter.avif'],
        ['Projekt', '/uploads/gallery/projekt-1.avif'],
        ['Projekt', '/uploads/gallery/projekt-2.avif'],
        ['Projekt', '/uploads/gallery/projekt-3.avif'],
      ];
      foreach ($singles as $s): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden">
        <img src="<?= e($s[1]) ?>" alt="<?= e($s[0]) ?>" loading="lazy" style="width:100%;height:220px;object-fit:cover">
        <p style="padding:14px 18px;font-weight:600;font-size:14.5px"><?= e($s[0]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal"><p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Bli vårt nästa projekt</p><h2 style="margin-bottom:8px">Redo för ett fast pris?</h2><p>Kostnadsfri offert inom 24 timmar.</p></div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-dark btn--lg">031-96 88 88</a>
        <a href="/offert" class="btn btn--primary btn--lg">Begär gratis offert</a>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
