<?php
$page_title       = 'Blogg – Råd & Tips om Tak och Fasad';
$page_description = 'Expertkunskap om takrenovering, fasadmålning och ROT-avdrag från M2 Bygg Team AB i Göteborg. Guider, priser och tips från lokala hantverkare.';
$active_page      = 'blogg';
$breadcrumbs      = [['Hem', '/'], ['Blogg', null]];
require_once __DIR__ . '/../crm/includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$posts = db()->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC")->fetchAll();

$cat_colors = [
  'tak'    => ['bg'=>'rgba(181,113,42,.12)','color'=>'var(--copper)','label'=>'Tak'],
  'fasad'  => ['bg'=>'rgba(25,103,255,.1)', 'color'=>'#1967FF',        'label'=>'Fasad'],
  'rot'    => ['bg'=>'rgba(5,150,105,.1)',  'color'=>'#059669',         'label'=>'ROT-avdrag'],
  'pris'   => ['bg'=>'rgba(109,40,217,.1)', 'color'=>'#7C3AED',         'label'=>'Priser'],
  'plat'   => ['bg'=>'rgba(107,114,128,.1)','color'=>'var(--steel)',     'label'=>'Plåt'],
  'mark'   => ['bg'=>'rgba(120,53,15,.1)',  'color'=>'#78350F',         'label'=>'Mark'],
  'ovrigt' => ['bg'=>'rgba(107,114,128,.1)','color'=>'var(--steel)',     'label'=>'Övrigt'],
];
function blog_cat(array $colors, string $key): array {
    return $colors[$key] ?? ['bg'=>'rgba(107,114,128,.1)','color'=>'var(--steel)','label'=>ucfirst($key)];
}
function blog_date_label(?string $dt): string {
    if (!$dt) return '';
    $months = ['jan','feb','mar','apr','maj','jun','jul','aug','sep','okt','nov','dec'];
    $ts = strtotime($dt);
    return (int)date('j', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
}
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Blogg</span>
</div></div></div>

<section class="hero" style="padding:76px 0 64px;text-align:center">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:560px;margin:0 auto">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);justify-content:center;margin:0 auto 18px">Expertkunskap från Göteborg</p>
      <h1 class="animate-in delay-1" style="margin-bottom:14px">Råd &amp; tips om<br>tak och fasad</h1>
      <p class="animate-in delay-2">Guider, råd och prisinfo om takarbeten, fasadrenovering och ROT-avdrag – från lokala hantverkare.</p>
    </div>
  </div>
</section>

<?php if ($posts): ?>
<!-- FILTER -->
<div style="background:var(--sand);border-bottom:1px solid var(--border);padding:0">
  <div class="container">
    <div style="display:flex;gap:0;overflow-x:auto">
      <?php
      $usedCats = array_unique(array_column($posts, 'category'));
      $filters = [['all', 'Alla inlägg']];
      foreach ($usedCats as $uc) $filters[] = [$uc, blog_cat($cat_colors, $uc)['label']];
      foreach ($filters as $i => [$val,$lbl]):
      ?>
      <button data-filter-val="<?= e($val) ?>"
              style="padding:16px 20px;border:none;background:transparent;font-family:var(--font-text);font-size:14px;font-weight:<?= $i===0?'500':'400' ?>;color:<?= $i===0?'var(--coal)':'var(--steel)' ?>;cursor:pointer;border-bottom:2px solid <?= $i===0?'var(--copper)':'transparent' ?>;transition:all .15s;white-space:nowrap"
              onclick="filterBlog(this)">
        <?= e($lbl) ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<section class="section">
  <div class="container">
    <?php if (!$posts): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--steel)">
      <p style="font-size:15px">Inga blogginlägg publicerade ännu — kom snart tillbaka!</p>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 320px;gap:48px;align-items:start">

      <!-- POSTS -->
      <div id="posts-grid">
        <?php foreach ($posts as $i => $post):
          $cc = blog_cat($cat_colors, $post['category']);
          $featured = $i === 0;
        ?>
        <?php if ($featured): ?>
        <article data-postcat="<?= e($post['category']) ?>"
                 style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;margin-bottom:20px;cursor:pointer;transition:all .3s var(--ease-out)"
                 onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-3px)'"
                 onmouseout="this.style.boxShadow='none';this.style.transform='translateY(0)'"
                 onclick="location.href='/blogg/<?= e($post['slug']) ?>'">
          <?php if ($post['cover_image']): ?>
          <div style="height:300px;overflow:hidden">
            <img src="<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;filter:brightness(.88)">
          </div>
          <?php endif; ?>
          <div style="padding:28px 30px 30px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap">
              <span style="background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px"><?= e($cc['label']) ?></span>
              <span style="font-size:13px;color:var(--steel-lt)"><?= e(blog_date_label($post['published_at'])) ?></span>
              <span style="font-size:13px;color:var(--steel-lt)">· <?= (int)$post['read_minutes'] ?> min läsning</span>
            </div>
            <h2 style="font-size:1.4rem;margin-bottom:10px;letter-spacing:-0.02em;line-height:1.2"><?= e($post['title']) ?></h2>
            <p style="font-size:15px;color:var(--steel);line-height:1.65;margin-bottom:18px"><?= e($post['excerpt']) ?></p>
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:14px;font-weight:500;color:var(--copper)">
              Läs hela artikeln
              <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
            </span>
          </div>
        </article>
        <?php else: ?>
        <article data-postcat="<?= e($post['category']) ?>"
                 style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;margin-bottom:14px;cursor:pointer;transition:all .3s var(--ease-out);display:grid;grid-template-columns:200px 1fr;position:relative"
                 onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.boxShadow='none';this.style.transform='translateY(0)'"
                 onclick="location.href='/blogg/<?= e($post['slug']) ?>'">
          <?php if ($post['cover_image']): ?>
          <div style="overflow:hidden">
            <img src="<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>" loading="lazy" style="width:100%;height:100%;min-height:150px;object-fit:cover;filter:brightness(.88)">
          </div>
          <?php else: ?>
          <div style="background:var(--surface)"></div>
          <?php endif; ?>
          <div style="padding:20px 22px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:9px;flex-wrap:wrap">
              <span style="background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:99px"><?= e($cc['label']) ?></span>
              <span style="font-size:12.5px;color:var(--steel-lt)"><?= e(blog_date_label($post['published_at'])) ?></span>
            </div>
            <h3 style="font-size:1rem;letter-spacing:-0.015em;line-height:1.3;margin-bottom:7px"><?= e($post['title']) ?></h3>
            <p style="font-size:13.5px;color:var(--steel);line-height:1.6;margin-bottom:12px"><?= e($post['excerpt']) ?></p>
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:500;color:var(--copper)">
              Läs mer
              <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
            </span>
          </div>
        </article>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <!-- SIDEBAR -->
      <aside style="position:sticky;top:90px;display:flex;flex-direction:column;gap:14px">
        <div style="background:linear-gradient(135deg,rgba(181,113,42,.12),rgba(181,113,42,.04));border:1px solid rgba(181,113,42,.2);border-radius:var(--r-xl);padding:22px">
          <h4 style="margin-bottom:7px">Få gratis offert</h4>
          <p style="font-size:13.5px;color:var(--steel);line-height:1.6;margin-bottom:16px">Redo att sätta igång? Fast pris inom 24 timmar.</p>
          <a href="/offert" class="btn btn--copper" style="width:100%;justify-content:center">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
            Begär gratis offert
          </a>
        </div>

        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
          <h4 style="margin-bottom:14px">Populära tjänster</h4>
          <?php foreach([['Takbyte','/tjanster/takbyte'],['Takmålning','/tjanster/takmalning'],['Fasadmålning','/tjanster/fasadmalning'],['Taktvätt','/tjanster/taktvatt'],['Plåtarbeten','/tjanster/platarbeten']] as $l): ?>
          <a href="<?= e($l[1]) ?>" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:var(--r-md);font-size:13.5px;color:var(--steel);transition:all .15s;margin-bottom:2px" onmouseover="this.style.color='var(--coal)';this.style.background='var(--sand-lt)'" onmouseout="this.style.color='var(--steel)';this.style.background='transparent'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--copper);flex-shrink:0"><path d="M9 18l6-6-6-6"/></svg>
            <?= e($l[0]) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
          <h4 style="margin-bottom:10px">Ring oss direkt</h4>
          <a href="tel:031968888" style="font-family:var(--font-display);font-weight:700;font-size:20px;color:var(--coal);letter-spacing:-0.02em;display:block;margin-bottom:4px">031-96 88 88</a>
          <div style="font-size:12.5px;color:var(--steel-lt)">Mån–Fre 07:00–18:00</div>
        </div>
      </aside>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
function filterBlog(btn) {
  const val = btn.dataset.filterVal;
  document.querySelectorAll('[data-filter-val]').forEach(b => {
    b.style.color = 'var(--steel)';
    b.style.borderBottomColor = 'transparent';
    b.style.fontWeight = '400';
  });
  btn.style.color = 'var(--coal)';
  btn.style.borderBottomColor = 'var(--copper)';
  btn.style.fontWeight = '500';
  document.querySelectorAll('[data-postcat]').forEach(el => {
    el.style.display = (val === 'all' || el.dataset.postcat === val) ? '' : 'none';
  });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
