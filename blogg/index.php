<?php
$page_title       = 'Blogg – Råd & Tips om Tak och Fasad';
$page_description = 'Expertkunskap om takrenovering, fasadmålning och ROT-avdrag från M2 Bygg Team AB i Göteborg. Guider, priser och tips från lokala hantverkare.';
$active_page      = 'blogg';
require_once __DIR__ . '/../includes/header.php';

$posts = [
  [
    'slug'    => 'takbyte-kostnad',
    'cat'     => 'tak',
    'cat_label'=> 'Tak',
    'date'    => '3 juni 2025',
    'read'    => '8 min',
    'img'     => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80',
    'title'   => 'Vad kostar ett takbyte 2025? Komplett prisguide för Göteborg',
    'excerpt' => 'Planerar du att byta tak? Vi går igenom alla kostnader – material, arbete, ROT-avdrag och vad som påverkar priset för tegeltak, betongtak och plåttak.',
    'featured'=> true,
  ],
  [
    'slug'    => 'rot-avdrag-guide',
    'cat'     => 'rot',
    'cat_label'=> 'ROT-avdrag',
    'date'    => '28 maj 2025',
    'read'    => '5 min',
    'img'     => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
    'title'   => 'ROT-avdrag 2025 – så fungerar det och hur mycket sparar du?',
    'excerpt' => 'Allt du behöver veta om ROT-avdraget 2025. Vi förklarar hur du ansöker, vad som gäller och hur mycket du faktiskt sparar på tak- och fasadarbeten.',
    'featured'=> false,
  ],
  [
    'slug'    => 'fasadmalning-guide',
    'cat'     => 'fasad',
    'cat_label'=> 'Fasad',
    'date'    => '15 maj 2025',
    'read'    => '6 min',
    'img'     => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
    'title'   => 'Fasadmålning – komplett guide: förberedelse, material och kostnader',
    'excerpt' => 'Hur förbereder du fasaden rätt? Vilket material håller längst i Göteborgs klimat? Vi svarar på allt du behöver veta inför din fasadmålning.',
    'featured'=> false,
  ],
  [
    'slug'    => 'taktvatt-varfor',
    'cat'     => 'tak',
    'cat_label'=> 'Tak',
    'date'    => '5 maj 2025',
    'read'    => '4 min',
    'img'     => 'https://images.unsplash.com/photo-1521336575822-6da63fb45455?w=800&q=80',
    'title'   => 'Varför ska man tvätta taket? 5 skäl att inte vänta',
    'excerpt' => 'Mossa och alger förstör ditt tak snabbare än du tror. Vi förklarar varför regelbunden taktvätt sparar dig tusenlappar – och hur du vet när det är dags.',
    'featured'=> false,
  ],
  [
    'slug'    => 'fasadmalning-pris',
    'cat'     => 'pris',
    'cat_label'=> 'Priser',
    'date'    => '22 apr 2025',
    'read'    => '5 min',
    'img'     => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=800&q=80',
    'title'   => 'Fasadmålning pris 2025 – vad kostar det per m² i Göteborg?',
    'excerpt' => 'Verkliga priser per m² för fasadmålning i Göteborg, uppdelade på fasadtyp och vad som ingår i ett fast pris. Med och utan ROT-avdrag.',
    'featured'=> false,
  ],
  [
    'slug'    => 'tegeltak-vs-betongtak',
    'cat'     => 'tak',
    'cat_label'=> 'Tak',
    'date'    => '10 apr 2025',
    'read'    => '6 min',
    'img'     => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80',
    'title'   => 'Tegeltak eller betongtak – vilket ska du välja?',
    'excerpt' => 'En ärlig jämförelse av livslängd, pris, underhåll och utseende. Vi hjälper dig välja rätt taktyp för ditt hus och budget i Göteborg.',
    'featured'=> false,
  ],
  [
    'slug'    => 'hangrannor-guide',
    'cat'     => 'plat',
    'cat_label'=> 'Plåt',
    'date'    => '2 apr 2025',
    'read'    => '4 min',
    'img'     => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=800&q=80',
    'title'   => 'Hängrännor – när ska de bytas och vad kostar det?',
    'excerpt' => 'Läckande eller rostiga hängrännor kan orsaka fuktskador på fasad och grund. Läs när det är dags att byta och vad du kan förvänta dig i pris.',
    'featured'=> false,
  ],
];

$cat_colors = [
  'tak'   => ['bg'=>'rgba(181,113,42,.12)','color'=>'var(--copper)','label'=>'Tak'],
  'fasad' => ['bg'=>'rgba(25,103,255,.1)', 'color'=>'#1967FF',        'label'=>'Fasad'],
  'rot'   => ['bg'=>'rgba(5,150,105,.1)',  'color'=>'#059669',         'label'=>'ROT-avdrag'],
  'pris'  => ['bg'=>'rgba(109,40,217,.1)', 'color'=>'#7C3AED',         'label'=>'Priser'],
  'plat'  => ['bg'=>'rgba(107,114,128,.1)','color'=>'var(--steel)',     'label'=>'Plåt'],
];
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

<!-- FILTER -->
<div style="background:var(--sand);border-bottom:1px solid var(--border);padding:0">
  <div class="container">
    <div style="display:flex;gap:0;overflow-x:auto">
      <?php
      $filters = [['all','Alla inlägg'],['tak','Tak'],['fasad','Fasad'],['rot','ROT-avdrag'],['pris','Priser'],['plat','Plåt']];
      foreach ($filters as $i => [$val,$lbl]):
      ?>
      <button data-filter-val="<?= $val ?>"
              style="padding:16px 20px;border:none;background:transparent;font-family:var(--font-text);font-size:14px;font-weight:<?= $i===0?'500':'400' ?>;color:<?= $i===0?'var(--coal)':'var(--steel)' ?>;cursor:pointer;border-bottom:2px solid <?= $i===0?'var(--copper)':'transparent' ?>;transition:all .15s;white-space:nowrap"
              onclick="filterBlog(this)"
              onmouseover="if(!this.classList.contains('active-tab')){this.style.color='var(--coal)'}"
              onmouseout="if(!this.classList.contains('active-tab')){this.style.color='var(--steel)'}">
        <?= $lbl ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:48px;align-items:start">

      <!-- POSTS -->
      <div id="posts-grid">
        <?php foreach ($posts as $i => $post):
          $cc = $cat_colors[$post['cat']] ?? $cat_colors['tak'];
        ?>
        <?php if ($post['featured']): ?>
        <!-- FEATURED -->
        <article data-postcat="<?= $post['cat'] ?>"
                 style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;margin-bottom:20px;cursor:pointer;transition:all .3s var(--ease-out)"
                 onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-3px)'"
                 onmouseout="this.style.boxShadow='none';this.style.transform='translateY(0)'"
                 onclick="location.href='/blogg/<?= $post['slug'] ?>'">
          <div style="height:300px;overflow:hidden">
            <img src="<?= $post['img'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy"
                 style="width:100%;height:100%;object-fit:cover;filter:brightness(.88);transition:transform .4s,filter .3s"
                 onmouseover="this.style.transform='scale(1.03)';this.style.filter='brightness(1)'"
                 onmouseout="this.style.transform='scale(1)';this.style.filter='brightness(.88)'">
          </div>
          <div style="padding:28px 30px 30px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap">
              <span style="background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;font-family:var(--font-text)"><?= $post['cat_label'] ?></span>
              <span style="font-size:13px;color:var(--steel-lt)"><?= $post['date'] ?></span>
              <span style="font-size:13px;color:var(--steel-lt)">· <?= $post['read'] ?> läsning</span>
            </div>
            <h2 style="font-size:1.4rem;margin-bottom:10px;letter-spacing:-0.02em;line-height:1.2"><?= htmlspecialchars($post['title']) ?></h2>
            <p style="font-size:15px;color:var(--steel);line-height:1.65;margin-bottom:18px"><?= htmlspecialchars($post['excerpt']) ?></p>
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:14px;font-weight:500;color:var(--copper)">
              Läs hela artikeln
              <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
            </span>
          </div>
        </article>

        <?php else: ?>
        <!-- SMALL POST -->
        <article data-postcat="<?= $post['cat'] ?>"
                 style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;margin-bottom:14px;cursor:pointer;transition:all .3s var(--ease-out);display:grid;grid-template-columns:200px 1fr;position:relative"
                 onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.boxShadow='none';this.style.transform='translateY(0)'"
                 onclick="location.href='/blogg/<?= $post['slug'] ?>'">
          <div style="overflow:hidden">
            <img src="<?= $post['img'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy"
                 style="width:100%;height:100%;min-height:150px;object-fit:cover;filter:brightness(.88);transition:transform .35s,filter .25s"
                 onmouseover="this.style.transform='scale(1.06)';this.style.filter='brightness(1)'"
                 onmouseout="this.style.transform='scale(1)';this.style.filter='brightness(.88)'">
          </div>
          <div style="padding:20px 22px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:9px;flex-wrap:wrap">
              <span style="background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:99px"><?= $post['cat_label'] ?></span>
              <span style="font-size:12.5px;color:var(--steel-lt)"><?= $post['date'] ?></span>
            </div>
            <h3 style="font-size:1rem;letter-spacing:-0.015em;line-height:1.3;margin-bottom:7px"><?= htmlspecialchars($post['title']) ?></h3>
            <p style="font-size:13.5px;color:var(--steel);line-height:1.6;margin-bottom:12px"><?= htmlspecialchars($post['excerpt']) ?></p>
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
          <a href="<?= $l[1] ?>" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:var(--r-md);font-size:13.5px;color:var(--steel);transition:all .15s;margin-bottom:2px" onmouseover="this.style.color='var(--coal)';this.style.background='var(--sand-lt)'" onmouseout="this.style.color='var(--steel)';this.style.background='transparent'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--copper);flex-shrink:0"><path d="M9 18l6-6-6-6"/></svg>
            <?= $l[0] ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
          <h4 style="margin-bottom:10px">Ring oss direkt</h4>
          <a href="tel:031968888" style="font-family:var(--font-display);font-weight:700;font-size:20px;color:var(--coal);letter-spacing:-0.02em;display:block;margin-bottom:4px">031-96 88 88</a>
          <div style="font-size:12.5px;color:var(--steel-lt)">Mån–Fre 07:00–18:00</div>
        </div>

        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
          <h4 style="margin-bottom:12px">Taggar</h4>
          <div style="display:flex;flex-wrap:wrap;gap:7px">
            <?php foreach(['Takbyte','ROT-avdrag','Fasadmålning','Göteborg','Tegeltak','Hängrännor','Taktvätt','Priser 2025'] as $tag): ?>
            <span style="background:var(--sand-lt);border:1px solid var(--border);border-radius:6px;padding:5px 11px;font-size:12.5px;color:var(--steel);cursor:pointer;transition:all .15s" onmouseover="this.style.borderColor='var(--copper)';this.style.color='var(--copper)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--steel)'"><?= $tag ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<script>
function filterBlog(btn) {
  const val = btn.dataset.filterVal;
  document.querySelectorAll('[data-filter-val]').forEach(b => {
    b.style.color = 'var(--steel)';
    b.style.borderBottomColor = 'transparent';
    b.style.fontWeight = '400';
    b.classList.remove('active-tab');
  });
  btn.style.color = 'var(--coal)';
  btn.style.borderBottomColor = 'var(--copper)';
  btn.style.fontWeight = '500';
  btn.classList.add('active-tab');
  document.querySelectorAll('[data-postcat]').forEach(el => {
    el.style.display = (val === 'all' || el.dataset.postcat === val) ? '' : 'none';
  });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
