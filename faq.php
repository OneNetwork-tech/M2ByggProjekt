<?php
$page_title       = 'Vanliga frågor – Tak, Fasad & ROT-avdrag';
$page_description = 'Svar på vanliga frågor om tak, fasad, priser och ROT-avdrag från M2 Bygg Team AB i Göteborg.';
$active_page      = '';
$breadcrumbs      = [['Hem', '/'], ['Vanliga frågor', null]];
require_once __DIR__ . '/includes/header.php';

$faqs = [
  'Priser' => [
    ['Vad kostar ett takbyte i Göteborg?', 'Kostnaden beror på takets storlek, lutning och material. För ett normalt villa (120–160 m²) kostar ett takbyte typiskt 120 000–250 000 kr. Med ROT-avdrag reduceras arbetskostnaden med 30%. Vi ger alltid ett fast pris efter kostnadsfri besiktning. Läs mer i vår prisguide.'],
    ['Vad kostar fasadmålning per m²?', 'Fasadmålning kostar normalt 180–320 kr/m² beroende på fasadtyp, skick och tillgänglighet. En villa på 200 m² kostar ca 36 000–64 000 kr. Med ROT-avdrag minskar kostnaden avsevärt. Vi ger alltid fast pris i skriftlig offert.'],
    ['Har ni fasta priser eller löpande räkning?', 'Vi använder alltid fast pris. Du vet exakt vad du betalar innan vi börjar – oavsett om jobbet tar kortare eller längre tid. Inga tillägg, inga överraskningar. Prisgaranti ingår i varje offert.'],
  ],
  'ROT-avdrag' => [
    ['Hur fungerar ROT-avdraget 2025?', 'ROT-avdraget ger 30% skattereduktion på arbetskostnaden (max 50 000 kr/år per person). Vi hanterar hela ansökan till Skatteverket – du betalar bara din andel direkt till oss. Material ingår inte i avdraget.'],
    ['Gäller ROT-avdrag på takbyte och fasadmålning?', 'Ja! Takbyte, takrenovering, takmålning, taktvätt, fasadmålning och fasadrenovering berättigar alla till ROT-avdrag. Vi redovisar alltid arbete och material separat för korrekt ROT-hantering.'],
    ['Hur mycket kan jag maximalt få i ROT-avdrag?', 'Maxbeloppet är 50 000 kr per person och år. Om ni är två på hushållet kan ni nyttja upp till 100 000 kr totalt. Avdraget är 30% av arbetskostnaden.'],
  ],
  'Takarbeten' => [
    ['Hur vet jag om mitt tak behöver bytas?', 'Tecken på att taket behöver åtgärdas: fuktfläckar inomhus, synlig mossväxt, skadade pannor, tak äldre än 30 år, eller genomdragning av kyla och fukt. Vi erbjuder gratis besiktning och ger alltid ett ärligt råd.'],
    ['Hur lång tid tar ett takbyte?', 'Ett takbyte på ett normalt villa (120–180 m²) tar vanligtvis 3–7 arbetsdagar beroende på taktyp och väder. Vi informerar alltid om tidsplan i förväg och täcker öppningarna varje kväll.'],
    ['Kan jag bo kvar under takbytet?', 'Ja, i de flesta fall. Vi täcker öppningarna varje kväll. Det kan vara bullrigt dagtid men vi informerar alltid om schema och vad som händer.'],
  ],
  'Fasadarbeten' => [
    ['Hur ofta behöver man måla om fasaden?', 'Det beror på material och klimat. Träfasader bör målas om var 8–12 år. Putsfasader klarar sig 15–20 år. Vi ger råd om rätt tidsintervall för just ditt hus vid besiktningen.'],
    ['Ingår färgkonsultation?', 'Ja, kostnadsfritt. Vi ger alltid råd om färgval baserat på hustyp, stil, omgivning och kommunens riktlinjer. Vi kan även visa hur din fasad ser ut med olika färger innan du bestämmer dig.'],
  ],
  'Garanti & Trygghet' => [
    ['Ger ni garanti på arbetet?', 'Om något problem uppstår till följd av vårt arbete åtgärdar vi det utan kostnad. Vi är fullständigt försäkrade.'],
    ['Är ni försäkrade?', 'Ja, vi har fullständig ansvarsförsäkring som täcker eventuella skador på din fastighet under utförandet. Alla hantverkare är yrkescertifierade. Du är alltid skyddad.'],
  ],
  'Process & Område' => [
    ['Hur snabbt kan ni komma ut för besiktning?', 'Vanligtvis inom 1–3 arbetsdagar i Göteborg. Akuta ärenden som läckage hanterar vi samma dag när det är möjligt. Ring 031-96 88 88 direkt för akuta fall.'],
    ['Var jobbar ni?', 'Vi är baserade i Hisings Backa, Göteborg, och utför uppdrag i hela Göteborg och Västsverige – Kungsbacka, Mölndal, Kungälv, Lerum, Alingsås, Borås, Trollhättan och alla orter däremellan.'],
  ],
];

$faqSchema = [
    '@context' => 'https://schema.org', '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($item) => [
        '@type' => 'Question', 'name' => $item[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item[1]],
    ], array_merge(...array_values($faqs))),
];
?>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_UNICODE) ?></script>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Vanliga frågor</span>
</div></div></div>

<section class="hero" style="padding:76px 0 64px;text-align:center">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div style="max-width:560px;margin:0 auto">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);justify-content:center;margin:0 auto 18px">Svar från experterna</p>
      <h1 class="animate-in delay-1" style="margin-bottom:14px">Vanliga frågor</h1>
      <p class="animate-in delay-2">Allt du undrar om priser, ROT-avdrag och process. Hittar du inte svaret? Ring 031-96 88 88.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:220px 1fr;gap:52px;align-items:start">

      <!-- Sidebar nav -->
      <nav style="position:sticky;top:90px;background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:18px" aria-label="FAQ kategorier">
        <div style="font-size:11px;font-weight:600;color:var(--steel-lt);letter-spacing:.09em;text-transform:uppercase;margin-bottom:12px">Kategorier</div>
        <?php foreach (array_keys($faqs) as $cat): ?>
        <a href="#<?= urlencode($cat) ?>"
           style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:var(--r-md);font-size:13.5px;color:var(--steel);transition:all .15s;margin-bottom:2px"
           onmouseover="this.style.color='var(--coal)';this.style.background='var(--sand-lt)'"
           onmouseout="this.style.color='var(--steel)';this.style.background='transparent'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--copper);flex-shrink:0"><path d="M9 18l6-6-6-6"/></svg>
          <?= $cat ?>
        </a>
        <?php endforeach; ?>
      </nav>

      <!-- FAQ content -->
      <div>
        <?php foreach ($faqs as $cat => $items): ?>
        <div id="<?= urlencode($cat) ?>" style="margin-bottom:44px;scroll-margin-top:90px">
          <h2 style="font-size:1.4rem;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--copper);display:inline-block"><?= $cat ?></h2>
          <div>
            <?php foreach ($items as $item): ?>
            <div class="faq-item reveal">
              <button class="faq-q">
                <?= htmlspecialchars($item[0]) ?>
                <div class="faq-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
              </button>
              <div class="faq-a">
                <div class="faq-a-inner"><?= htmlspecialchars($item[1]) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-band">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap">
      <div class="reveal">
        <p class="eyebrow" style="color:var(--copper-lt);margin-bottom:12px">Fick du inte svar?</p>
        <h2 style="margin-bottom:8px">Ring oss direkt</h2>
        <p>Vi svarar alltid snabbt och ger dig ett ärligt råd – utan förpliktelser.</p>
      </div>
      <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
