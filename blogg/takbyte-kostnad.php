<?php
$page_title       = 'Vad kostar ett takbyte 2025? Komplett prisguide för Göteborg';
$page_description = 'Vad kostar ett takbyte 2025 i Göteborg? Prisguide med verkliga priser för tegeltak, betongtak och plåttak. Med och utan ROT-avdrag. M2 Bygg Team AB.';
$active_page      = 'blogg';
$extra_js         = '<script>window.addEventListener("scroll",()=>{const h=document.documentElement;document.getElementById("rp").style.width=(h.scrollTop/(h.scrollHeight-h.clientHeight)*100)+"%"});</script>';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="reading-progress" id="rp"></div>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/blogg">Blogg</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Takbyte kostnad 2025</span>
</div></div></div>

<!-- ARTICLE HERO -->
<div style="position:relative;height:440px;overflow:hidden">
  <img src="https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1400&q=80"
       alt="Takbyte kostnad Göteborg 2025"
       style="width:100%;height:100%;object-fit:cover;filter:brightness(.55)">
  <div style="position:absolute;inset:0;background:linear-gradient(0,rgba(29,29,31,.95) 0%,rgba(29,29,31,.35) 60%,transparent 100%);display:flex;align-items:flex-end">
    <div class="container" style="padding-bottom:36px">
      <span style="background:rgba(181,113,42,.9);color:#fff;font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;display:inline-block;margin-bottom:14px">Tak</span>
      <h1 style="color:#fff;max-width:700px;font-size:clamp(1.6rem,3.5vw,2.6rem);margin-bottom:14px">Vad kostar ett takbyte 2025?<br>Komplett prisguide för Göteborg</h1>
      <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
        <span style="font-size:13.5px;color:rgba(255,255,255,.6)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;margin-right:4px"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
          3 juni 2025
        </span>
        <span style="font-size:13.5px;color:rgba(255,255,255,.6)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;margin-right:4px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          8 min läsning
        </span>
        <span style="font-size:13.5px;color:rgba(255,255,255,.6)">M2 Bygg Team AB</span>
      </div>
    </div>
  </div>
</div>

<!-- ARTICLE LAYOUT -->
<div style="max-width:1200px;margin:0 auto;padding:52px 32px;display:grid;grid-template-columns:1fr 300px;gap:56px;align-items:start">

  <article style="font-family:var(--font-text);color:var(--steel);font-size:16px;line-height:1.72">

    <!-- QUICK SUMMARY -->
    <div style="background:rgba(181,113,42,.07);border:1px solid rgba(181,113,42,.2);border-radius:var(--r-xl);padding:24px 28px;margin-bottom:36px">
      <h2 style="font-size:1.1rem;color:var(--coal);margin-bottom:16px">Snabb sammanfattning – priser 2025</h2>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden">
        <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;background:rgba(181,113,42,.08);border-bottom:1px solid var(--border)">
          <?php foreach(['Taktyp','Pris exkl. ROT','Pris inkl. ROT','Livslängd'] as $h): ?>
          <div style="padding:10px 14px;font-size:11px;font-weight:600;color:var(--copper);letter-spacing:.07em;text-transform:uppercase"><?= $h ?></div>
          <?php endforeach; ?>
        </div>
        <?php foreach([
          ['Tegeltak (150 m²)','160 000–220 000 kr','120 000–165 000 kr','50–100 år'],
          ['Betongpannor (150 m²)','130 000–180 000 kr','100 000–135 000 kr','30–50 år'],
          ['Plåttak (150 m²)','140 000–200 000 kr','105 000–150 000 kr','40–70 år'],
          ['Papptak (150 m²)','80 000–130 000 kr','62 000–100 000 kr','20–30 år'],
        ] as $r): ?>
        <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;border-bottom:1px solid rgba(0,0,0,.04)">
          <div style="padding:11px 14px;font-size:14px;color:var(--coal);font-weight:500"><?= $r[0] ?></div>
          <div style="padding:11px 14px;font-size:13.5px;color:var(--steel);font-family:var(--font-display);font-weight:600"><?= $r[1] ?></div>
          <div style="padding:11px 14px;font-size:13.5px;color:#059669;font-family:var(--font-display);font-weight:600"><?= $r[2] ?></div>
          <div style="padding:11px 14px;font-size:13px;color:var(--copper)"><?= $r[3] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <h2 style="font-family:var(--font-display);font-size:1.7rem;letter-spacing:-0.025em;color:var(--coal);margin:0 0 14px">Vad ingår i priset för ett takbyte?</h2>
    <p>Priset för ett takbyte inkluderar normalt följande:</p>
    <ul style="padding-left:22px;display:flex;flex-direction:column;gap:7px;margin:14px 0 20px">
      <?php foreach(['Demontering av gammalt tak – pannor, papp eller plåt tas bort','Kontroll av underlag – takstolar, råspont och vindskivor inspekteras','Åtgärd av underlag – skadade delar byts ut (kostnad tillkommer vid större skador)','Nytt undertäcksmaterial – diffusionsöppen takduk','Nytt täckmaterial – pannor, plåt eller papp beroende på taktyp','Nockpanna, vindskivor och beslag runt skorsten, takluckor och takfot','Transport och ställning – ingår i de flesta fall','Städning – vi lämnar tomt och rent'] as $item): ?>
      <li style="font-size:15px;color:var(--steel)"><?= $item ?></li>
      <?php endforeach; ?>
    </ul>

    <!-- HIGHLIGHT BOX -->
    <div style="background:rgba(181,113,42,.08);border:1px solid rgba(181,113,42,.2);border-radius:var(--r-lg);padding:18px 22px;margin:24px 0;display:flex;gap:14px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--copper);flex-shrink:0;margin-top:2px"><path d="M9 14l6-6M9.5 8.5a.5.5 0 110-1 .5.5 0 010 1zm5 5a.5.5 0 110-1 .5.5 0 010 1z"/><rect x="3" y="3" width="18" height="18" rx="3"/></svg>
      <div>
        <strong style="display:block;color:var(--copper);font-size:14px;margin-bottom:5px">ROT-avdraget sparar dig 30% av arbetskostnaden</strong>
        <p style="font-size:14px;margin:0;line-height:1.65">ROT-avdraget täcker 30% av arbetskostnaden (max 50 000 kr/år/person). Material ingår inte – men vi redovisar alltid separat och hanterar hela ansökan åt dig.</p>
      </div>
    </div>

    <h2 style="font-family:var(--font-display);font-size:1.7rem;letter-spacing:-0.025em;color:var(--coal);margin:36px 0 14px;padding-top:28px;border-top:1px solid var(--border)">Vad påverkar priset på ett takbyte?</h2>

    <h3 style="font-family:var(--font-display);font-size:1.15rem;color:var(--coal);margin:22px 0 8px">1. Takstorlek</h3>
    <p>Priset beror direkt på takets yta. Mät takets längd × bredd och multiplicera med lutningsfaktorn (normalt 1,3–1,6 för ett normalt sadeltak). En villa med 120 m² bottenyta kan ha 150–190 m² takyta.</p>

    <h3 style="font-family:var(--font-display);font-size:1.15rem;color:var(--coal);margin:22px 0 8px">2. Taklutning</h3>
    <p>Ett brantare tak kräver mer ställning och säkerhetsutrustning. Tak med lutning över 45° kan kosta 15–25% mer i arbete. Flacka tak är enklare att arbeta på men kräver bättre vattentäthet.</p>

    <h3 style="font-family:var(--font-display);font-size:1.15rem;color:var(--coal);margin:22px 0 8px">3. Befintligt underlag</h3>
    <p>Vid en besiktning tittar vi alltid på råspont, takstolar och vindskivor. Om dessa är skadade av fukt eller insekter tillkommer kostnad för reparation – men vi identifierar alltid skador och redovisar dem i offerten.</p>

    <h3 style="font-family:var(--font-display);font-size:1.15rem;color:var(--coal);margin:22px 0 8px">4. Material och taktyp</h3>
    <p>Tegeltak är dyrare i material men håller längst. Plåttak i stål eller zink kostar mer men kräver nästan inget underhåll. Betongpannor är vanliga och prisvärda. Papptak är billigast men kortast livslängd.</p>

    <h2 style="font-family:var(--font-display);font-size:1.7rem;letter-spacing:-0.025em;color:var(--coal);margin:36px 0 14px;padding-top:28px;border-top:1px solid var(--border)">Takbyte Göteborg – verkliga projektpriser</h2>
    <p>Baserat på projekt vi utfört i Göteborg och Västsverige under 2024–2025:</p>

    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;margin:20px 0">
      <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;background:rgba(181,113,42,.07);border-bottom:1px solid var(--border)">
        <?php foreach(['Projektexempel','Takyta','Totalt','Inkl. ROT'] as $h): ?>
        <div style="padding:10px 14px;font-size:11px;font-weight:600;color:var(--copper);letter-spacing:.07em;text-transform:uppercase"><?= $h ?></div>
        <?php endforeach; ?>
      </div>
      <?php foreach([
        ['Villa, Kungsbacka – tegeltak','165 m²','195 000 kr','148 000 kr'],
        ['Villa, Mölndal – betongpannor','140 m²','158 000 kr','122 000 kr'],
        ['Villa, Hovås – plåttak','120 m²','172 000 kr','130 000 kr'],
        ['Radhus, Lerum – papptak','90 m²','98 000 kr','76 000 kr'],
      ] as $r): ?>
      <div style="display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;border-bottom:1px solid rgba(0,0,0,.04);transition:background .1s" onmouseover="this.style.background='rgba(0,0,0,.02)'" onmouseout="this.style.background='transparent'">
        <div style="padding:11px 14px;font-size:14px;color:var(--coal)"><?= $r[0] ?></div>
        <div style="padding:11px 14px;font-family:var(--font-display);font-weight:600;font-size:13px;color:var(--steel)"><?= $r[1] ?></div>
        <div style="padding:11px 14px;font-family:var(--font-display);font-weight:600;font-size:13px;color:var(--steel)"><?= $r[2] ?></div>
        <div style="padding:11px 14px;font-family:var(--font-display);font-weight:600;font-size:13px;color:#059669"><?= $r[3] ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <h2 style="font-family:var(--font-display);font-size:1.7rem;letter-spacing:-0.025em;color:var(--coal);margin:36px 0 14px;padding-top:28px;border-top:1px solid var(--border)">Steg-för-steg: Så går ett takbyte med M2 till</h2>
    <div style="display:flex;flex-direction:column;gap:14px;margin:20px 0 24px">
      <?php foreach([
        ['1','Kontakt & foton','Ring eller ladda upp bilder. Vi bedömer projektet och bokar in en kostnadsfri besiktning.'],
        ['2','Fast offert','Du får en skriftlig offert med exakt fast pris. Inga dolda kostnader – aldrig.'],
        ['3','Förberedelse','Vi täcker och förbereder noggrant innan vi börjar demontera det gamla taket.'],
        ['4','Klart & godkänt','Du inspekterar resultatet. Vi lämnar inte förrän du är 100% nöjd. 5 år garanti utfärdas.'],
      ] as $step): ?>
      <div style="display:flex;gap:14px;align-items:flex-start;background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:16px 18px">
        <div style="min-width:36px;height:36px;background:var(--copper);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;flex-shrink:0"><?= $step[0] ?></div>
        <div>
          <div style="font-family:var(--font-display);font-weight:600;font-size:15px;color:var(--coal);margin-bottom:4px"><?= $step[1] ?></div>
          <div style="font-size:14px;color:var(--steel);line-height:1.6"><?= $step[2] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- INLINE CTA -->
    <div style="background:var(--coal);border-radius:var(--r-xl);padding:32px;margin:36px 0;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;position:relative;overflow:hidden">
      <div style="position:absolute;top:-60px;left:-50px;width:240px;height:240px;background:radial-gradient(circle,rgba(181,113,42,.15) 0%,transparent 65%);pointer-events:none"></div>
      <div style="position:relative">
        <div style="font-family:var(--font-display);font-weight:700;font-size:1.2rem;color:#fff;margin-bottom:5px">Få ett fast pris på ditt takbyte</div>
        <div style="font-size:14px;color:rgba(245,245,247,.55)">Gratis besiktning och offert inom 24 timmar.</div>
      </div>
      <div style="display:flex;gap:10px;flex-shrink:0;flex-wrap:wrap;position:relative">
        <a href="tel:031968888" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:980px;padding:11px 20px;color:#fff;font-family:var(--font-text);font-size:15px;font-weight:400;transition:all .18s" onmouseover="this.style.background='rgba(255,255,255,.18)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--copper-lt)"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          031-96 88 88
        </a>
        <a href="/offert" class="btn btn--copper">Begär offert</a>
      </div>
    </div>

    <!-- AUTHOR -->
    <div style="background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px 22px;display:flex;gap:14px;align-items:center;margin-top:20px">
      <div style="width:52px;height:52px;background:rgba(181,113,42,.15);border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:18px;color:var(--copper);flex-shrink:0">m2</div>
      <div>
        <div style="font-family:var(--font-display);font-weight:600;font-size:15px;color:var(--coal);margin-bottom:3px">M2 Bygg Team AB</div>
        <div style="font-size:12.5px;color:var(--steel-lt);margin-bottom:5px">Professionella hantverkare i Göteborg</div>
        <div style="font-size:13.5px;color:var(--steel);line-height:1.6">Vi utför takbyten, takmålningar och takrenovaeringar i Göteborg och hela Västsverige. 1 000+ nöjda kunder, fast pris och 5 år garanti. Ring 031-96 88 88.</div>
      </div>
    </div>

    <!-- RELATED -->
    <div style="margin-top:36px;padding-top:28px;border-top:1px solid var(--border)">
      <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--coal);margin-bottom:16px">Relaterade artiklar</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <?php foreach([
          ['/blogg/rot-avdrag-guide','ROT-avdrag 2025 – hur fungerar det?'],
          ['/blogg/tegeltak-vs-betongtak','Tegeltak eller betongtak – vilket ska du välja?'],
        ] as $rel): ?>
        <a href="<?= $rel[0] ?>" style="background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:14px 16px;font-size:14px;color:var(--coal);transition:all .18s;display:flex;align-items:center;gap:8px" onmouseover="this.style.borderColor='var(--copper)';this.style.background='var(--white)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--sand-lt)'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--copper);flex-shrink:0"><path d="M9 18l6-6-6-6"/></svg>
          <?= $rel[1] ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </article>

  <!-- SIDEBAR -->
  <aside style="position:sticky;top:90px;display:flex;flex-direction:column;gap:14px">
    <div style="background:linear-gradient(135deg,rgba(181,113,42,.12),rgba(181,113,42,.04));border:1px solid rgba(181,113,42,.2);border-radius:var(--r-xl);padding:22px">
      <h4 style="margin-bottom:7px">Få ett exakt pris</h4>
      <p style="font-size:13.5px;color:var(--steel);line-height:1.6;margin-bottom:16px">Fast pris på ditt takbyte inom 24 timmar.</p>
      <a href="/offert" class="btn btn--copper" style="width:100%;justify-content:center">Begär offert</a>
    </div>
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
      <h4 style="margin-bottom:12px">Innehåll</h4>
      <?php foreach(['Snabb prisöversikt','Vad ingår i priset?','Vad påverkar priset?','Verkliga projekt i Göteborg','Steg-för-steg process'] as $toc): ?>
      <div style="display:flex;align-items:center;gap:7px;padding:6px 8px;font-size:13.5px;color:var(--steel);cursor:pointer;border-radius:var(--r-md);transition:all .15s" onmouseover="this.style.color='var(--coal)';this.style.background='var(--sand-lt)'" onmouseout="this.style.color='var(--steel)';this.style.background='transparent'">
        <span style="width:4px;height:4px;background:var(--copper);border-radius:50%;flex-shrink:0"></span>
        <?= $toc ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px">
      <h4 style="margin-bottom:10px">Ring oss</h4>
      <a href="tel:031968888" style="font-family:var(--font-display);font-weight:700;font-size:20px;color:var(--coal);letter-spacing:-0.02em;display:block;margin-bottom:4px">031-96 88 88</a>
      <a href="tel:0732405026" style="font-size:15px;color:var(--steel-lt);display:block;margin-bottom:6px">0732-40 50 26</a>
      <div style="font-size:12.5px;color:var(--steel-lt)">Mån–Fre 07:00–18:00</div>
    </div>
  </aside>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
