<?php
$page_title       = 'Tak & Fasad Alingsås – Fast Pris & ROT-avdrag';
$page_description = 'M2 Bygg Team AB – tak, fasad och plåtarbeten i Alingsås. Fast pris och ROT-avdrag. Lokala hantverkare. Ring 031-96 88 88.';
$active_page      = '';
$breadcrumbs      = [['Hem', '/'], ['Alingsås', null]];
require_once __DIR__ . '/../includes/header.php';
?>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"LocalBusiness","name":"M2 Bygg Team AB","telephone":["+46319688880","+46732405026"],"address":{"@type":"PostalAddress","streetAddress":"Lillhagsvagen 88","postalCode":"442 43","addressLocality":"Hisings Backa","addressRegion":"Vastra Gotaland","addressCountry":"SE"},"geo":{"@type":"GeoCoordinates","latitude":57.9295,"longitude":12.5339},"areaServed":"Alingsås"}
</script>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Tak &amp; Fasad Alingsås</span>
</div></div></div>

<section class="hero" style="padding:80px 0 70px">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:600px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:18px">Lokala hantverkare</p>
      <h1 class="animate-in delay-1" style="margin-bottom:14px">Tak &amp; fasad<br>i Alingsås</h1>
      <p class="animate-in delay-2">Professionella takarbeten och fasadrenoveringar i Alingsås med fast pris..</p>
      <ul style="list-style:none;margin:24px 0 28px;display:flex;flex-direction:column;gap:10px" class="animate-in delay-3">
        <?php foreach(['Fast pris – prisgaranti ingår alltid','ROT-avdrag – vi hanterar ansökan','Kostnadsfri offert','Svar och offert inom 24 timmar'] as $c): ?>
        <li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?=$c?>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="animate-in delay-4" style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="/offert" class="btn btn--copper btn--lg">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
          Begär gratis offert
        </a>
        <a href="tel:031968888" class="btn btn--outline-white btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          031-96 88 88
        </a>
      </div>
    </div>
  </div>
</section>

<div class="trust-strip"><div class="container"><div class="trust-strip__grid">
<?php foreach([['Fast pris','Prisgaranti alltid'],['ROT-avdrag','Vi hanterar ansökan'],['Kostnadsfri offert','Inom 24h'],['Svar 24h','Gratis offert']] as $t): ?>
<div class="trust-item"><div class="trust-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="trust-item__text"><strong><?=$t[0]?></strong><span><?=$t[1]?></span></div></div>
<?php endforeach; ?>
</div></div></div>

<section class="section">
  <div class="container">
    <div class="section-header reveal" style="margin-bottom:32px">
      <p class="eyebrow">Tjänster i Alingsås</p>
      <h2>Vad vi gör i Alingsås</h2>
      <p>Alla tjänster med fast pris och ROT-avdrag.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px" class="reveal-group">
      <?php foreach([
        ['Takbyte','/tjanster/takbyte','Komplett takbyte i alla material. Tegeltak, betongtak, plåttak.','från 900 kr/m²'],
        ['Takmålning','/tjanster/takmalning','Förnyar och skyddar taket i 10–15 år.','från 150 kr/m²'],
        ['Fasadmålning','/tjanster/fasadmalning','Förvandlar och skyddar fasaden. Fri färgkonsultation.','från 180 kr/m²'],
        ['Taktvätt','/tjanster/taktvatt','Mossbehandling och högtryckstvätt. ROT-avdrag.','från 80 kr/m²'],
      ] as $s): ?>
      <a href="<?=$s[1]?>" class="service-card reveal">
        <div class="service-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
        <span class="price-badge" style="margin-bottom:10px"><?=$s[3]?></span>
        <h3><?=$s[0]?> i Alingsås</h3>
        <p><?=$s[2]?></p>
        <span class="service-card__link">Läs mer <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--sand-lt)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start">
      <div class="reveal">
        <p class="eyebrow" style="margin-bottom:14px">Lokal närvaro</p>
        <h2 style="margin-bottom:12px">Alingsås är<br>vårt område</h2>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:16px">Professionella takarbeten och fasadrenoveringar i Alingsås med fast pris.</p>
        <p style="color:var(--steel);line-height:1.72;margin-bottom:24px">Vi har genomfört 70+ uppdrag i Alingsås och känner till lokala förutsättningar, klimat och byggkrav. Det märks i kvaliteten.</p>
        <h4 style="font-size:14px;color:var(--coal);margin-bottom:12px">Områden vi täcker i Alingsås</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <a href="/offert" style="display:flex;align-items:center;gap:8px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 13px;font-size:13.5px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Alingsås centrum</a>
      <a href="/offert" style="display:flex;align-items:center;gap:8px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 13px;font-size:13.5px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Nödinge</a>
      <a href="/offert" style="display:flex;align-items:center;gap:8px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 13px;font-size:13.5px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Ingared</a>
      <a href="/offert" style="display:flex;align-items:center;gap:8px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 13px;font-size:13.5px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Sollebrunn</a>
      <a href="/offert" style="display:flex;align-items:center;gap:8px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 13px;font-size:13.5px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Bjärlanda</a>
      <a href="/offert" style="display:flex;align-items:center;gap:8px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 13px;font-size:13.5px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Skållerud</a>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:12px" class="reveal-group">
        <?php foreach([['70+','Projekt i Alingsås'],['4,9/5','Google-betyg'],['24h','Svarstid'],['5 år','Garanti']] as $s): ?>
        <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px 24px">
          <div style="font-family:var(--font-display);font-size:2.4rem;font-weight:700;letter-spacing:-0.04em;color:var(--coal);line-height:1;margin-bottom:5px"><?=$s[0]?></div>
          <div style="font-size:13.5px;color:var(--steel)"><?=$s[1]?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" style="background:var(--coal)">
  <div class="container">
    <div class="section-header reveal" style="margin-bottom:28px">
      <p class="eyebrow" style="color:var(--copper-lt)">Kundrecensioner</p>
      <h2 style="color:var(--sand-lt)">Vad kunder i Alingsås säger</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px" class="reveal-group">
      <?php foreach([
        ['JA','Johan A.','Anlitade M2 för takbyte i Alingsås. Professionellt, fast pris och exakt i tid. Rekommenderas!'],
        ['ML','Maria L.','Fasadmålning – otrolig förvandling. ROT-avdraget hanterades smidigt. Toppenklass!'],
        ['EK','Erik K.','Snabb respons och toppenservice. M2 är det bästa valet i Alingsås. 5 av 5!'],
      ] as $r): ?>
      <div class="review-card reveal" style="background:var(--coal-soft);border-color:rgba(255,255,255,.07)">
        <div class="review-card__stars">★★★★★</div>
        <p class="review-card__text">"<?=$r[2]?>"</p>
        <div class="review-card__author">
          <div class="review-card__avatar" style="background:rgba(181,113,42,.2)"><?=$r[0]?></div>
          <div><div class="review-card__name" style="color:var(--sand-lt)"><?=$r[1]?></div><div class="review-card__loc">Alingsås · Google</div></div>
          <div class="review-card__g">G</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal">
        <p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Behöver du hjälp i Alingsås?</p>
        <h2 style="margin-bottom:8px">Gratis offert inom 24h</h2>
        <p>Lokala hantverkare och fast pris.</p>
      </div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-white btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          031-96 88 88
        </a>
        <a href="/offert" class="btn btn--copper btn--lg">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
          Gratis offert
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
