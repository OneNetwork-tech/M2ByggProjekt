<?php
/**
 * Public service detail page — fallback renderer for services added via the CRM that
 * don't have a hand-built static page (tjanster/{slug}.php). Routed via /tjanster/{slug}
 * when no literal file exists (see .htaccess / router.php).
 */
require_once __DIR__ . '/../crm/includes/db.php';

$slug = $_GET['slug'] ?? '';
$s = db()->prepare("SELECT * FROM services WHERE slug = ? AND visible = 1");
$s->execute([$slug]);
$service = $s->fetch();

if (!$service) {
    http_response_code(404);
    require_once __DIR__ . '/../404.php';
    exit;
}

$page_title       = $service['title'] . ' – M2 Bygg Team AB';
$page_description = $service['description'] ?: $service['title'] . ' från M2 Bygg Team AB. Fast pris, ROT-avdrag och 5 år garanti i Göteborg och Västsverige.';
$active_page      = 'tjanster';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/tjanster">Tjänster</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span><?= e($service['title']) ?></span>
</div></div></div>

<?php if ($service['cover_image']): ?>
<div style="position:relative;height:380px;overflow:hidden">
  <img src="<?= e($service['cover_image']) ?>" alt="<?= e($service['title']) ?>" style="width:100%;height:100%;object-fit:cover;filter:brightness(.55)">
  <div style="position:absolute;inset:0;background:linear-gradient(0,rgba(29,29,31,.95) 0%,rgba(29,29,31,.35) 60%,transparent 100%);display:flex;align-items:flex-end">
    <div class="container" style="padding-bottom:32px">
      <span style="background:rgba(181,113,42,.9);color:#fff;font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;display:inline-block;margin-bottom:12px"><?= e($service['category']) ?></span>
      <h1 style="color:#fff;max-width:700px;font-size:clamp(1.5rem,3.2vw,2.3rem);margin-bottom:10px"><?= e($service['title']) ?></h1>
      <?php if ($service['price_label']): ?><div style="font-size:14px;color:rgba(255,255,255,.7)"><?= e($service['price_label']) ?></div><?php endif; ?>
    </div>
  </div>
</div>
<?php else: ?>
<div class="container" style="padding-top:48px">
  <span style="background:rgba(181,113,42,.12);color:var(--copper);font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;display:inline-block;margin-bottom:14px"><?= e($service['category']) ?></span>
  <h1 style="max-width:780px;margin-bottom:10px"><?= e($service['title']) ?></h1>
  <?php if ($service['price_label']): ?><div style="font-size:14px;color:var(--steel-lt);margin-bottom:20px"><?= e($service['price_label']) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<div class="container" style="max-width:760px;padding:48px 24px 20px">
  <?php if ($service['description']): ?>
  <p style="font-size:17px;color:var(--steel);line-height:1.6;margin-bottom:28px"><?= e($service['description']) ?></p>
  <?php endif; ?>
  <?php if ($service['detail_body']): ?>
  <div class="blog-body"><?= $service['detail_body'] ?></div>
  <?php endif; ?>
</div>

<!-- CTA -->
<section class="cta-band">
  <div class="container" style="text-align:center">
    <div class="reveal">
      <p class="eyebrow" style="color:var(--copper-lt);justify-content:center;margin:0 auto 14px">Redo att komma igång?</p>
      <h2 style="margin-bottom:8px">Få fast pris på <?= e(mb_strtolower($service['title'])) ?></h2>
      <p style="margin:0 auto 28px;max-width:440px">Gratis besiktning och fast offert inom 24 timmar. Inga förpliktelser.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="tel:031968888" class="btn btn--outline-white btn--lg">031-96 88 88</a>
        <a href="/offert" class="btn btn--copper btn--lg">Begär gratis offert</a>
      </div>
    </div>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
