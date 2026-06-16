<?php
$page_title       = 'Projekt & Portfolio – Verkliga resultat';
$page_description = 'Se våra projekt – M2 Bygg Team AB. Takbyten, fasadrenoveringar, plåtarbeten och markarbeten i Göteborg och Västsverige.';
$active_page      = 'projekt';
require_once __DIR__ . '/includes/header.php';

$projects = [
  ['cat'=>'tak','img'=>'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=700&q=80','title'=>'Takbyte Kungsbacka','sub'=>'Tegeltak · 165 m² · 2025','h'=>280],
  ['cat'=>'fasad','img'=>'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=700&q=80','title'=>'Fasadmålning Mölndal','sub'=>'Träfasad · 220 m² · 2025','h'=>340],
  ['cat'=>'plat','img'=>'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=700&q=80','title'=>'Plåtarbete Hovås','sub'=>'Hängrännor & stuprör · 2024','h'=>220],
  ['cat'=>'mark','img'=>'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=700&q=80','title'=>'Stenläggning Askim','sub'=>'Natursten terrass · 45 m² · 2024','h'=>300],
  ['cat'=>'fasad','img'=>'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=700&q=80','title'=>'Fasadrenovering Kungälv','sub'=>'Putsfasad · 180 m² · 2024','h'=>250],
  ['cat'=>'tak','img'=>'https://images.unsplash.com/photo-1521336575822-6da63fb45455?w=700&q=80','title'=>'Taktvätt Alingsås','sub'=>'Mossbehandling · 140 m² · 2024','h'=>210],
  ['cat'=>'tak','img'=>'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=700&q=80','title'=>'Takmålning Lerum','sub'=>'Betongpannor · 130 m² · 2024','h'=>260],
  ['cat'=>'fasad','img'=>'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=700&q=80','title'=>'Fasadtvätt Hisingen','sub'=>'Högtryckstvätt · 2024','h'=>230],
  ['cat'=>'mark','img'=>'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=700&q=80','title'=>'Markarbete Härryda','sub'=>'Dränering & schaktning · 2023','h'=>290],
];
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Projekt</span>
</div></div></div>

<section class="hero" style="padding:80px 0 70px;text-align:center">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:560px;margin:0 auto">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);justify-content:center;margin:0 auto 18px">1 000+ slutförda projekt</p>
      <h1 class="animate-in delay-1" style="margin-bottom:14px">Verkliga resultat</h1>
      <p class="animate-in delay-2">Alla bilder är från verkliga uppdrag vi utfört i Göteborg och Västsverige.</p>
    </div>
  </div>
</section>

<!-- STATS -->
<div style="background:var(--coal);border-bottom:1px solid rgba(255,255,255,.06)">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr)">
      <?php foreach([['1 000','+','Slutförda projekt'],['4,9','/5','Google-betyg'],['20','+','Orter i Västsverige'],['5',' år','Garanti alltid']] as $i=>$s): ?>
      <div style="text-align:center;padding:28px 20px;border-right:1px solid rgba(255,255,255,.07);<?= $i===3?'border-right:none':'' ?>">
        <div style="font-family:var(--font-display);font-size:2.4rem;font-weight:700;letter-spacing:-0.04em;color:var(--sand-lt);line-height:1;margin-bottom:6px">
          <?= $s[0] ?><em style="font-style:normal;color:var(--copper-lt)"><?= $s[1] ?></em>
        </div>
        <div style="font-size:13px;color:rgba(245,245,247,.4)"><?= $s[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- FILTER + GALLERY -->
<section class="section">
  <div class="container">
    <!-- Filter bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:12px">
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <?php
        $filters = [['all','Alla projekt'],['tak','Tak'],['fasad','Fasad'],['plat','Plåt'],['mark','Mark']];
        foreach ($filters as $i=>[$cat,$label]):
        ?>
        <button data-filter-btn
          onclick="filterServices(this,'<?= $cat ?>')"
          style="padding:8px 18px;border-radius:980px;border:1px solid <?= $i===0?'var(--coal)':'var(--border)' ?>;background:<?= $i===0?'var(--coal)':'transparent' ?>;color:<?= $i===0?'var(--sand-lt)':'var(--steel)' ?>;font-family:var(--font-text);font-size:13.5px;cursor:pointer;transition:all .18s"
          class="<?= $i===0?'active':'' ?>"
          onmouseover="if(!this.classList.contains('active')){this.style.borderColor='var(--coal)';this.style.color='var(--coal)';}"
          onmouseout="if(!this.classList.contains('active')){this.style.borderColor='var(--border)';this.style.color='var(--steel)';}">
          <?= $label ?>
        </button>
        <?php endforeach; ?>
      </div>
      <span style="font-size:13.5px;color:var(--steel)">Visar <strong id="filter-count"><?= count($projects) ?></strong> projekt</span>
    </div>

    <!-- Masonry -->
    <div style="columns:3;column-gap:14px" id="proj-grid">
      <?php foreach ($projects as $p): ?>
      <div data-cat="<?= $p['cat'] ?>"
           style="break-inside:avoid;margin-bottom:14px;border-radius:var(--r-xl);overflow:hidden;position:relative;cursor:pointer;transition:transform .25s var(--ease-out),box-shadow .25s"
           onmouseover="this.querySelector('.proj-overlay').style.opacity='1';this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-xl)'"
           onmouseout="this.querySelector('.proj-overlay').style.opacity='0';this.style.transform='translateY(0)';this.style.boxShadow='none'"
           onclick="openLightbox('<?= $p['img'] ?>','<?= $p['title'] ?>','<?= $p['sub'] ?>')">
        <img src="<?= $p['img'] ?>" alt="<?= $p['title'] ?>" loading="lazy"
             style="width:100%;height:<?= $p['h'] ?>px;object-fit:cover;display:block;filter:brightness(.85);transition:filter .3s">
        <div class="proj-overlay" style="position:absolute;inset:0;background:linear-gradient(0,rgba(29,29,31,.88) 0%,transparent 55%);opacity:0;transition:opacity .25s;display:flex;flex-direction:column;justify-content:flex-end;padding:18px">
          <div style="display:flex;gap:6px;margin-bottom:7px">
            <span style="background:var(--copper);color:#fff;font-size:11px;font-weight:600;padding:3px 9px;border-radius:99px;font-family:var(--font-text)"><?= ucfirst($p['cat']) ?></span>
          </div>
          <div style="font-family:var(--font-display);font-weight:600;font-size:15px;color:#fff;margin-bottom:3px"><?= $p['title'] ?></div>
          <div style="font-size:12.5px;color:rgba(255,255,255,.6)"><?= $p['sub'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LIGHTBOX -->
<div id="lightbox" style="position:fixed;inset:0;background:rgba(0,0,0,.92);backdrop-filter:blur(12px);z-index:700;display:none;align-items:center;justify-content:center;padding:20px" onclick="if(event.target===this)closeLightbox()">
  <div style="max-width:900px;width:100%;position:relative">
    <button onclick="closeLightbox()" style="position:absolute;top:-48px;right:0;width:40px;height:40px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;transition:background .15s;font-size:18px" onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">✕</button>
    <img id="lb-img" src="" alt="" style="width:100%;max-height:80vh;object-fit:contain;border-radius:var(--r-xl)">
    <div style="margin-top:14px;text-align:center">
      <div id="lb-title" style="font-family:var(--font-display);font-weight:600;font-size:18px;color:#fff;margin-bottom:4px"></div>
      <div id="lb-sub" style="font-size:13.5px;color:rgba(255,255,255,.5)"></div>
    </div>
  </div>
</div>

<!-- CTA -->
<section class="cta-band">
  <div class="container" style="text-align:center">
    <div class="reveal">
      <p class="eyebrow" style="color:var(--copper-lt);justify-content:center;margin:0 auto 14px">Vill du ha ett liknande resultat?</p>
      <h2 style="margin-bottom:8px">Kontakta oss idag</h2>
      <p style="margin:0 auto 28px;max-width:440px">Gratis besiktning och fast offert inom 24 timmar. Inga förpliktelser.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
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

<script>
// Override filter for masonry layout
document.querySelectorAll('[data-filter-btn]').forEach(btn => {
  btn.addEventListener('click', function() {
    const cat = this.onclick.toString().match(/'([^']+)'\)/)[1];
    document.querySelectorAll('[data-filter-btn]').forEach(b => {
      b.style.background = 'transparent';
      b.style.color = 'var(--steel)';
      b.style.borderColor = 'var(--border)';
      b.classList.remove('active');
    });
    this.style.background = 'var(--coal)';
    this.style.color = 'var(--sand-lt)';
    this.style.borderColor = 'var(--coal)';
    this.classList.add('active');
    let count = 0;
    document.querySelectorAll('#proj-grid [data-cat]').forEach(el => {
      const show = cat === 'all' || el.dataset.cat === cat;
      el.style.display = show ? '' : 'none';
      if (show) count++;
    });
    document.getElementById('filter-count').textContent = count;
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
