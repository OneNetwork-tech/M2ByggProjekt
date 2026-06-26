<?php
$page_title       = 'Bli Partner – Jobba med M2 Bygg Team AB';
$page_description = 'Bli M2 Partner – ansök som hantverkare eller underleverantör i Göteborg. Fler uppdrag, starkt varumärke och snabb betalning. Ansök nu.';
$active_page      = '';
$breadcrumbs      = [['Hem', '/'], ['Bli partner', null]];
require_once __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Bli partner</span>
</div></div></div>

<!-- HERO -->
<section class="hero" style="padding:84px 0 72px">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="display:grid;grid-template-columns:1fr 420px;gap:56px;align-items:center">
      <div>
        <span class="badge" style="background:rgba(181,113,42,.2);color:var(--copper-lt);margin-bottom:20px;display:inline-flex;animation:pulse 2.2s infinite">
          <span style="width:5px;height:5px;background:var(--copper-lt);border-radius:50%;margin-right:6px"></span>
          Nu öppet för ansökningar
        </span>
        <h1 class="animate-in" style="margin-bottom:16px">Jobba med oss –<br>bli M2 Partner</h1>
        <p class="animate-in delay-1" style="margin-bottom:28px;max-width:460px">Vi söker duktiga hantverkare och underleverantörer i Göteborg. Bli en del av M2-nätverket och få fler uppdrag, fast betalning och digitalt stöd.</p>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:11px" class="animate-in delay-2">
          <?php foreach([
            'Fler uppdrag – vi skickar kunder direkt till dig',
            'M2-varumärket och 4,9/5 i Google-betyg bakom dig',
            'Fast och snabb betalning – alltid i tid',
            'Långsiktigt samarbete – vi väljer noggrant och stannar',
          ] as $c): ?>
          <li style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.78);font-size:15px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;color:var(--copper-lt);flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= $c ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap" class="animate-in delay-3">
          <a href="#ansok" class="btn btn--copper btn--lg">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
            Ansök som partner
          </a>
          <a href="tel:031968888" class="btn btn--outline-white btn--lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
            Ring oss
          </a>
        </div>
      </div>

      <!-- APPLICATION FORM (desktop) -->
      <div id="ansok" style="background:rgba(245,245,247,.06);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:var(--r-xl);padding:30px;scroll-margin-top:90px">
        <h3 style="font-size:1.1rem;color:#fff;margin-bottom:4px">Ansök som M2 Partner</h3>
        <p style="font-size:13px;color:rgba(255,255,255,.45);margin-bottom:20px">Vi svarar inom 2 arbetsdagar</p>

        <div id="partner-success" style="display:none;background:rgba(5,150,105,.15);border:1px solid rgba(5,150,105,.3);border-radius:var(--r-md);padding:18px;text-align:center;margin-bottom:10px">
          <div style="font-size:22px;margin-bottom:8px">✓</div>
          <div style="font-family:var(--font-display);font-weight:600;color:#fff;margin-bottom:6px">Ansökan mottagen!</div>
          <div style="font-size:13.5px;color:rgba(255,255,255,.6)">Vi granskar och återkommer inom 2 arbetsdagar.</div>
        </div>

        <form id="partner-form" action="/send/partner.php" method="POST" novalidate>
          <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div style="margin-bottom:10px">
              <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">Företagsnamn</label>
              <input type="text" name="company" placeholder="Ditt företag AB" required style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:#fff;font-family:var(--font-text);font-size:14px;outline:none;transition:border-color .18s" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'" placeholder="Ditt AB">
            </div>
            <div style="margin-bottom:10px">
              <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">Org.nummer</label>
              <input type="text" name="orgnr" placeholder="556XXX-XXXX" style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:#fff;font-family:var(--font-text);font-size:14px;outline:none;transition:border-color .18s" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div style="margin-bottom:10px">
              <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">Kontaktperson</label>
              <input type="text" name="contact" required placeholder="Namn" style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:#fff;font-family:var(--font-text);font-size:14px;outline:none;transition:border-color .18s" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'">
            </div>
            <div style="margin-bottom:10px">
              <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">Telefon</label>
              <input type="tel" name="phone" required placeholder="07X-XXX XX XX" style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:#fff;font-family:var(--font-text);font-size:14px;outline:none;transition:border-color .18s" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'">
            </div>
          </div>

          <div style="margin-bottom:10px">
            <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">E-post</label>
            <input type="email" name="email" required placeholder="ditt@foretag.se" style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:#fff;font-family:var(--font-text);font-size:14px;outline:none;transition:border-color .18s" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'">
          </div>

          <div style="margin-bottom:10px">
            <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">Specialitet</label>
            <select name="specialty" required style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:rgba(255,255,255,.7);font-family:var(--font-text);font-size:14px;outline:none;transition:border-color .18s;-webkit-appearance:none" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'">
              <option value="">Välj specialitet...</option>
              <option>Takarbeten (läggare)</option>
              <option>Takarbeten (målning)</option>
              <option>Fasadmålning</option>
              <option>Fasadrenovering</option>
              <option>Plåtarbeten</option>
              <option>Markarbete & Stenläggning</option>
              <option>Klottersanering</option>
              <option>Allmän byggservice</option>
            </select>
          </div>

          <div style="margin-bottom:14px">
            <label style="display:block;font-size:10.5px;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:5px">Om ditt företag</label>
            <textarea name="about" rows="3" placeholder="Erfarenhet, certifieringar, antal anställda..." style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:var(--r-md);padding:10px 13px;color:#fff;font-family:var(--font-text);font-size:14px;outline:none;resize:vertical;transition:border-color .18s" onfocus="this.style.borderColor='var(--copper)'" onblur="this.style.borderColor='rgba(255,255,255,.15)'"></textarea>
          </div>

          <button type="submit" class="btn btn--copper btn--lg" style="width:100%;justify-content:center">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
            Skicka ansökan
          </button>
          <p style="font-size:11.5px;color:rgba(255,255,255,.25);margin-top:10px;display:flex;align-items:center;gap:5px;line-height:1.5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;color:var(--copper);flex-shrink:0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Din ansökan behandlas konfidentiellt
          </p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div style="background:var(--copper)">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr)">
      <?php foreach([['8+','Aktiva partners'],['50+','Uppdrag/mån'],['4,9/5','Partnerbetyg'],['100%','Betalar alltid i tid']] as $i=>$s): ?>
      <div style="padding:22px;text-align:center;border-right:1px solid rgba(255,255,255,.2);<?= $i===3?'border-right:none':'' ?>">
        <div style="font-family:var(--font-display);font-size:2rem;font-weight:700;letter-spacing:-0.04em;color:#fff;line-height:1;margin-bottom:5px"><?= $s[0] ?></div>
        <div style="font-size:13px;color:rgba(255,255,255,.75)"><?= $s[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- BENEFITS -->
<section class="section">
  <div class="container">
    <div class="section-header reveal" style="text-align:center;align-items:center;margin-bottom:40px">
      <p class="eyebrow" style="margin:0 auto 14px">Fördelar med M2 Partner</p>
      <h2 style="margin-bottom:10px">Varför bli M2 Partner?</h2>
      <p style="margin:0 auto;max-width:500px">Vi ger dig det du behöver för att fokusera på det du är bäst på – hantverket.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="reveal-group">
      <?php
      $bens = [
        ['<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','Fler kunder','Vi skickar kvalificerade kunder direkt till dig. Du slipper marknadsföring – det sköter vi.'],
        ['<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','M2-varumärket','Du arbetar under M2-varumärket med 4,9/5 på Google och 1 000+ nöjda kunder bakom dig.'],
        ['<path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>','Digital plattform','Hantera uppdrag, kommunicera med M2 och kunder, uppdatera status – allt digitalt.'],
        ['<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>','Snabb betalning','Vi betalar alltid i tid enligt avtalade villkor. Inga förseningar – du kan planera din ekonomi.'],
        ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','Trygghet & stöd','M2 hanterar kundkontakt, offerter och kontrakt. Du fokuserar på jobbet.'],
        ['<path d="M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3"/>','Långsiktigt','Vi väljer partners noggrant och bygger långsiktiga relationer med hög kontinuitet.'],
      ];
      foreach ($bens as $b): ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:var(--s8);transition:all .3s var(--ease-out);position:relative;overflow:hidden" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='none';this.style.transform='translateY(0)'">
        <div style="width:50px;height:50px;background:var(--sand-lt);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;margin-bottom:var(--s5)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--copper)"><?= $b[0] ?></svg>
        </div>
        <h3 style="font-size:1.05rem;margin-bottom:7px"><?= $b[1] ?></h3>
        <p style="font-size:14px;color:var(--steel);line-height:1.62"><?= $b[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" style="background:var(--sand-lt)">
  <div class="container">
    <div class="section-header reveal" style="text-align:center;align-items:center;margin-bottom:40px">
      <p class="eyebrow" style="margin:0 auto 14px">Hur det fungerar</p>
      <h2>Från ansökan till första uppdrag</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;position:relative" class="reveal-group">
      <div style="position:absolute;top:34px;left:calc(12.5% + 26px);right:calc(12.5% + 26px);height:1.5px;background:linear-gradient(90deg,var(--copper),rgba(181,113,42,.1))"></div>
      <?php foreach([
        ['1','Ansök','Fyll i formuläret ovan. Tar 3 minuter. Vi svarar inom 2 arbetsdagar.'],
        ['2','Intervju','Kort genomgång av kvalifikationer, erfarenhet och verksamhetsområde.'],
        ['3','Avtal','Enkla och rättvisa villkor. Tydligt partneravtal utan konstigheter.'],
        ['4','Första uppdrag','Du är redo. Vi skickar ditt första uppdrag – du är officiellt M2 Partner.'],
      ] as $step): ?>
      <div class="reveal" style="padding:0 18px;text-align:center">
        <div style="width:68px;height:68px;border-radius:50%;background:var(--white);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;position:relative;z-index:1;box-shadow:var(--shadow-sm);transition:all .25s" onmouseover="this.style.borderColor='var(--copper)';this.style.boxShadow='0 6px 24px rgba(181,113,42,.15)'" onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='var(--shadow-sm)'">
          <div style="width:48px;height:48px;border-radius:50%;background:var(--coal);display:flex;align-items:center;justify-content:center">
            <span style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff"><?= $step[0] ?></span>
          </div>
        </div>
        <h4 style="margin-bottom:6px"><?= $step[1] ?></h4>
        <p style="font-size:13.5px;color:var(--steel);line-height:1.65"><?= $step[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- REQUIREMENTS -->
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start">
      <div>
        <p class="eyebrow reveal" style="margin-bottom:14px">Krav på partners</p>
        <h2 class="reveal" style="margin-bottom:8px">Vad vi kräver</h2>
        <p class="reveal" style="color:var(--steel);margin-bottom:28px">Vi väljer noggrant för att garantera kvalitet mot våra kunder.</p>
        <div style="display:flex;flex-direction:column;gap:12px" class="reveal-group">
          <?php foreach([
            ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','Registrerat företag','F-skattsedel och momsregistrering. Enskild firma eller aktiebolag.'],
            ['<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>','Certifiering eller erfarenhet','Yrkescertifiering eller minst 3 år dokumenterad erfarenhet.'],
            ['<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','Ansvarsförsäkring','Aktiv ansvarsförsäkring för yrkesmässig verksamhet.'],
            ['<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>','Kommunikationsförmåga','Snabb respons och professionell kommunikation med kunder och M2.'],
          ] as $r): ?>
          <div class="reveal" style="display:flex;align-items:flex-start;gap:13px;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:16px 18px">
            <div style="width:38px;height:38px;background:rgba(181,113,42,.1);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px;color:var(--copper)"><?= $r[0] ?></svg>
            </div>
            <div>
              <h4 style="margin-bottom:3px;font-size:14px"><?= $r[1] ?></h4>
              <p style="font-size:13.5px;color:var(--steel);line-height:1.55"><?= $r[2] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="reveal">
        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:28px;margin-bottom:14px">
          <div style="color:#D97706;font-size:14px;letter-spacing:2px;margin-bottom:14px">★★★★★</div>
          <p style="font-size:15px;color:var(--steel);line-height:1.7;font-style:italic;margin-bottom:18px">"Att bli M2 Partner har förändrat vår verksamhet. Vi behöver inte längre leta efter kunder – de kommer till oss via M2. Betalningen är alltid i tid."</p>
          <div style="display:flex;align-items:center;gap:11px">
            <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--sand);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:15px;color:var(--copper);flex-shrink:0">TB</div>
            <div>
              <div style="font-family:var(--font-display);font-weight:600;font-size:14.5px;color:var(--coal)">Tak & Bygg AB</div>
              <div style="font-size:12.5px;color:var(--steel-lt)">M2 Partner sedan 2022 · Göteborg</div>
            </div>
          </div>
        </div>
        <div style="background:rgba(181,113,42,.07);border:1px solid rgba(181,113,42,.18);border-radius:var(--r-xl);padding:22px">
          <div style="font-size:11px;font-weight:600;color:var(--copper);letter-spacing:.09em;text-transform:uppercase;margin-bottom:10px">Frågor om partnerskap?</div>
          <p style="font-size:14px;color:var(--steel);line-height:1.65;margin-bottom:14px">Ring oss direkt så berättar vi mer om hur M2 Partner fungerar.</p>
          <a href="tel:031968888" style="font-family:var(--font-display);font-weight:700;font-size:19px;color:var(--coal);letter-spacing:-0.02em;display:block;margin-bottom:4px">031-96 88 88</a>
          <a href="mailto:info@m2team.se" style="font-size:13.5px;color:var(--steel-lt)">info@m2team.se</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal"><p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Redo att ansöka?</p><h2 style="margin-bottom:8px">Fyll i formuläret ovan</h2><p>Vi svarar alltid inom 2 arbetsdagar.</p></div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
        <a href="tel:031968888" class="btn btn--outline-white btn--lg"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>031-96 88 88</a>
        <a href="#ansok" class="btn btn--copper btn--lg"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>Ansök som partner</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
