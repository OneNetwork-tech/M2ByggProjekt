<?php
$page_title       = 'Integritetspolicy – M2 Bygg Team AB';
$page_description = 'Integritetspolicy för M2 Bygg Team AB – hur vi behandlar dina personuppgifter i enlighet med GDPR.';
$active_page      = '';
require_once __DIR__ . '/includes/header.php';
$year = date('Y');
?>

<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span>Integritetspolicy</span>
</div></div></div>

<!-- HEADER -->
<div style="background:var(--coal);padding:60px 0 52px;position:relative;overflow:hidden">
  <div style="position:absolute;top:-80px;right:-60px;width:400px;height:400px;background:radial-gradient(circle,rgba(181,113,42,.08) 0%,transparent 65%);pointer-events:none"></div>
  <div class="container" style="position:relative;z-index:2">
    <div style="max-width:640px">
      <span class="badge badge--copper animate-in" style="margin-bottom:16px;display:inline-flex">GDPR-dokument</span>
      <h1 class="animate-in delay-1" style="margin-bottom:10px">Integritetspolicy</h1>
      <p class="animate-in delay-2" style="color:rgba(245,245,247,.55)">Hur M2 Bygg Team AB samlar in, behandlar och skyddar dina personuppgifter.</p>
      <div class="animate-in delay-3" style="display:flex;flex-wrap:wrap;gap:18px;margin-top:18px">
        <?php foreach([
          ['<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>','Gäller från 1 januari 2025'],
          ['<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/>','Uppdaterad juni 2025'],
          ['<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>','M2 Bygg Team AB'],
        ] as $m): ?>
        <span style="display:flex;align-items:center;gap:6px;font-size:13px;color:rgba(245,245,247,.45)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--copper)"><?= $m[0] ?></svg>
          <?= $m[1] ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- DOCUMENT -->
<div style="max-width:1100px;margin:0 auto;padding:52px 32px 80px;display:grid;grid-template-columns:1fr 240px;gap:56px;align-items:start">

  <article>
    <?php
    $sections = [
      ['1', 'Personuppgiftsansvarig', [
        ['text', 'M2 Bygg Team AB är personuppgiftsansvarig för behandlingen av dina personuppgifter.'],
        ['contact'],
      ]],
      ['2', 'Vilka uppgifter samlar vi in?', [
        ['text', 'Vi samlar in personuppgifter som du frivilligt lämnar när du kontaktar oss, begär offert eller anlitar oss.'],
        ['table',
          ['Typ','Exempel','Källa'],
          [
            ['Kontaktuppgifter','Namn, telefon, e-post','Offertformulär, telefon'],
            ['Adressuppgifter','Fastighetens adress och ort','Offertförfrågan'],
            ['Projektuppgifter','Beskrivning, bilder','Formulär, e-post'],
            ['Betalningsinformation','Fakturauppgifter, ROT','Vid affär'],
            ['Kommunikation','E-post, SMS','Löpande kontakt'],
          ]
        ],
        ['text', 'Vi samlar även in teknisk information vid besök på m2team.se: anonymiserad IP-adress, webbläsartyp, besökta sidor och hänvisande webbplats.'],
      ]],
      ['3', 'Varför behandlar vi dina uppgifter?', [
        ['table',
          ['Ändamål','Beskrivning'],
          [
            ['Offert och kundkontakt','Besvara förfrågningar, boka besiktningar och hålla kontakt under projektet.'],
            ['Utföra uppdrag','Genomföra beställda tjänster och koordinera med underleverantörer.'],
            ['Fakturering','Skapa fakturor, hantera betalningar och ROT-ansökan.'],
            ['Bokföring','Uppfylla krav i bokföringslagen och skattelagstiftningen.'],
            ['Webbanalys','Förstå hur webbplatsen används (anonymiserad statistik).'],
          ]
        ],
      ]],
      ['4', 'Rättslig grund', [
        ['text', 'Vi behandlar dina personuppgifter med stöd av följande rättsliga grunder (GDPR artikel 6):'],
        ['list', [
          '<strong>Avtal (art. 6.1.b)</strong> – utföra beställt arbete och fakturera.',
          '<strong>Rättslig förpliktelse (art. 6.1.c)</strong> – bokföringslagen och skattelagstiftning.',
          '<strong>Berättigat intresse (art. 6.1.f)</strong> – webbanalys och marknadsföring till befintliga kunder.',
          '<strong>Samtycke (art. 6.1.a)</strong> – vid cookies och direktmarknadsföring.',
        ]],
      ]],
      ['5', 'Hur länge sparas uppgifterna?', [
        ['table',
          ['Kategori','Lagringstid','Grund'],
          [
            ['Kunduppgifter (offert ej accepterad)','12 månader','Berättigat intresse'],
            ['Kunduppgifter (genomfört uppdrag)','3 år efter avslutat','Garanti/reklamation'],
            ['Bokföringsunderlag','7 år','Bokföringslagen'],
            ['ROT-dokumentation','7 år','Skattelagstiftning'],
            ['Webbanalysdata','14 månader','GA4-standard'],
          ]
        ],
      ]],
      ['6', 'Delar vi uppgifter med tredje part?', [
        ['highlight', 'Vi säljer aldrig dina uppgifter. Dina personuppgifter delas aldrig med reklamföretag eller liknande parter.'],
        ['text', 'Vi kan dela uppgifter med: underleverantörer (bundna av sekretessavtal), Skatteverket (ROT-ansökan), Google Analytics (anonymiserad statistik) och bokföringsprogram.'],
      ]],
      ['7', 'Cookies och spårning', [
        ['table',
          ['Typ','Syfte','Lagring'],
          [
            ['Nödvändiga cookies','Webbplatsens grundfunktion','Session'],
            ['Analyticscookies (GA4)','Anonym besöksstatistik','14 månader'],
            ['Marknadsföringscookies','Används ej','–'],
          ]
        ],
      ]],
      ['8', 'Dina rättigheter', [
        ['table',
          ['Rättighet','Innebär att du kan'],
          [
            ['Tillgång','Begära en kopia av de uppgifter vi har om dig.'],
            ['Rättelse','Begära att felaktiga uppgifter korrigeras.'],
            ['Radering','Begära att uppgifter raderas om rättslig grund saknas.'],
            ['Begränsning','Begära begränsad behandling under utredning.'],
            ['Dataportabilitet','Få ut dina uppgifter i maskinläsbart format.'],
            ['Invändning','Invända mot behandling baserad på berättigat intresse.'],
          ]
        ],
        ['text', 'Skicka din begäran till info@m2team.se. Vi svarar inom 30 dagar. Du kan även klaga till Integritetsskyddsmyndigheten (IMY) på imy.se.'],
      ]],
      ['9', 'Säkerhet', [
        ['list', [
          'HTTPS-kryptering på hela webbplatsen',
          'Begränsad tillgång – endast behörig personal',
          'Krypterad e-post vid behov',
          'Regelbunden säkerhetsöversyn',
        ]],
      ]],
      ['10', 'Kontakt', [
        ['text', 'Har du frågor om vår personuppgiftsbehandling? Kontakta oss:'],
        ['contact'],
      ]],
    ];
    ?>

    <?php foreach ($sections as [$num, $title, $content]): ?>
    <div id="s<?= $num ?>" style="margin-bottom:44px;scroll-margin-top:90px">
      <h2 style="display:flex;align-items:center;gap:10px;font-size:1.2rem;margin-bottom:16px">
        <span style="min-width:30px;height:30px;background:rgba(181,113,42,.1);border:1px solid rgba(181,113,42,.2);border-radius:7px;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--copper);flex-shrink:0"><?= $num ?></span>
        <?= $title ?>
      </h2>

      <?php foreach ($content as $block):
        $type = $block[0];
        if ($type === 'text'): ?>
        <p style="font-size:15px;color:var(--steel);line-height:1.72;margin-bottom:12px"><?= $block[1] ?></p>

        <?php elseif ($type === 'list'): ?>
        <ul style="padding-left:18px;display:flex;flex-direction:column;gap:8px;margin-bottom:14px">
          <?php foreach ($block[1] as $li): ?>
          <li style="font-size:14.5px;color:var(--steel);line-height:1.65"><?= $li ?></li>
          <?php endforeach; ?>
        </ul>

        <?php elseif ($type === 'table'):
          $headers = $block[1];
          $rows    = $block[2];
          $cols = count($headers);
          $gridCol = implode(' ', array_fill(0, $cols, '1fr'));
          if ($cols === 3 && strlen($headers[0]) < 20) $gridCol = '1.5fr 1fr 1fr';
        ?>
        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;margin-bottom:16px">
          <div style="display:grid;grid-template-columns:<?= $gridCol ?>;background:rgba(181,113,42,.07);border-bottom:1px solid var(--border)">
            <?php foreach ($headers as $h): ?>
            <div style="padding:10px 14px;font-size:11px;font-weight:600;color:var(--copper);letter-spacing:.07em;text-transform:uppercase"><?= $h ?></div>
            <?php endforeach; ?>
          </div>
          <?php foreach ($rows as $row): ?>
          <div style="display:grid;grid-template-columns:<?= $gridCol ?>;border-bottom:1px solid rgba(0,0,0,.04)">
            <?php foreach ($row as $i => $cell): ?>
            <div style="padding:11px 14px;font-size:13.5px;color:<?= $i===0 ? 'var(--coal);font-weight:500' : 'var(--steel)' ?>;line-height:1.55"><?= $cell ?></div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <?php elseif ($type === 'highlight'): ?>
        <div style="background:rgba(181,113,42,.07);border:1px solid rgba(181,113,42,.2);border-radius:var(--r-lg);padding:16px 20px;margin-bottom:12px;display:flex;gap:12px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;color:var(--copper);flex-shrink:0;margin-top:1px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <p style="font-size:14px;color:var(--steel);line-height:1.65;margin:0"><strong style="color:var(--copper)"><?= $block[1] ?></strong></p>
        </div>

        <?php elseif ($type === 'contact'): ?>
        <div style="background:var(--sand-lt);border:1px solid var(--border);border-radius:var(--r-lg);padding:20px;margin-top:10px">
          <?php
          $rows = [
            ['<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>','M2 Bygg Team AB',null],
            ['<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>','Lillhagsvägen 88, 442 43 Hisings Backa',null],
            ['<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/>','031-96 88 88  ·  0732-40 50 26','tel:031968888'],
            ['<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>','info@m2team.se','mailto:info@m2team.se'],
          ];
          foreach ($rows as $r):
            $tag = $r[2] ? 'a href="'.$r[2].'"' : 'div';
            $endtag = $r[2] ? 'a' : 'div';
          ?>
          <<?= $tag ?> style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--steel);margin-bottom:10px;transition:color .15s<?= $r[2]?';color:var(--coal)':'' ?>" <?= $r[2]?'onmouseover="this.style.color=\'var(--copper)\'" onmouseout="this.style.color=\'var(--steel)\'"':'' ?>>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--copper);flex-shrink:0"><?= $r[0] ?></svg>
            <?= $r[1] ?>
          </<?= $endtag ?>>
          <?php endforeach; ?>
        </div>

        <?php endif; ?>
      <?php endforeach; ?>

    </div>
    <hr style="border:none;border-top:1px solid var(--border);margin:0">
    <?php endforeach; ?>

    <p style="font-size:13px;color:var(--steel-lt);margin-top:32px;text-align:center">
      Denna policy gäller från 1 januari 2025. © <?= $year ?> M2 Bygg Team AB.
    </p>
  </article>

  <!-- TOC SIDEBAR -->
  <nav style="position:sticky;top:90px;background:var(--white);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px" aria-label="Innehållsförteckning">
    <div style="font-size:11px;font-weight:600;color:var(--steel-lt);letter-spacing:.09em;text-transform:uppercase;margin-bottom:12px">Innehåll</div>
    <?php foreach ($sections as [$num, $title, $_]): ?>
    <a href="#s<?= $num ?>" style="display:flex;align-items:center;gap:8px;padding:7px 9px;border-radius:var(--r-md);font-size:13px;color:var(--steel);transition:all .15s;margin-bottom:2px" onmouseover="this.style.color='var(--coal)';this.style.background='var(--sand-lt)'" onmouseout="this.style.color='var(--steel)';this.style.background='transparent'">
      <span style="font-family:var(--font-display);font-size:11px;font-weight:700;color:var(--copper);min-width:16px"><?= $num ?>.</span>
      <?= $title ?>
    </a>
    <?php endforeach; ?>
  </nav>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
