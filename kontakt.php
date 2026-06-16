<?php
$page_title       = 'Kontakt & Gratis Offert';
$page_description = 'Kontakta M2 Bygg Team AB – Ring 031-96 88 88, skicka e-post till info@m2team.se eller fyll i formuläret. Gratis offert inom 24h. Lillhagsvägen 88, Hisings Backa.';
$active_page      = 'kontakt';
require_once __DIR__ . '/includes/header.php';
?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <div class="container">
    <div class="breadcrumb__inner">
      <a href="/">Hem</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
      <span>Kontakt</span>
    </div>
  </div>
</div>

<!-- HERO MINI -->
<section style="background:var(--coal);padding:60px 0 52px;position:relative;overflow:hidden">
  <div style="position:absolute;top:-100px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(181,113,42,.1) 0%,transparent 65%);pointer-events:none"></div>
  <div class="container" style="position:relative;z-index:2;text-align:center">
    <p class="eyebrow animate-in" style="color:var(--copper-lt);justify-content:center;margin-bottom:14px">Svar inom 24 timmar</p>
    <h1 class="animate-in delay-1" style="color:var(--sand-lt);margin-bottom:12px">Kontakta oss</h1>
    <p class="animate-in delay-2" style="color:rgba(246,244,240,.55);font-size:1.05rem;max-width:480px;margin:0 auto">Ring, maila eller fyll i formuläret – vi återkommer med ett fast pris utan förpliktelser.</p>
  </div>
</section>

<!-- QUICK CONTACT BAR -->
<div style="background:var(--copper);padding:0">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(3,1fr)">
      <a href="tel:031968888" style="display:flex;align-items:center;gap:13px;padding:20px 24px;border-right:1px solid rgba(255,255,255,.2);transition:background .15s" onmouseover="this.style.background='rgba(0,0,0,.08)'" onmouseout="this.style.background='transparent'">
        <div style="width:44px;height:44px;background:rgba(255,255,255,.18);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:20px;height:20px"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
        </div>
        <div>
          <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.1em;text-transform:uppercase;margin-bottom:3px">Telefon</div>
          <div style="font-size:17px;font-weight:700;color:#fff">031-96 88 88</div>
          <div style="font-size:12px;color:rgba(255,255,255,.65)">Mån–Fre 07:00–18:00</div>
        </div>
      </a>
      <a href="tel:0732405026" style="display:flex;align-items:center;gap:13px;padding:20px 24px;border-right:1px solid rgba(255,255,255,.2);transition:background .15s" onmouseover="this.style.background='rgba(0,0,0,.08)'" onmouseout="this.style.background='transparent'">
        <div style="width:44px;height:44px;background:rgba(255,255,255,.18);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:20px;height:20px"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
        </div>
        <div>
          <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.1em;text-transform:uppercase;margin-bottom:3px">Mobil</div>
          <div style="font-size:17px;font-weight:700;color:#fff">0732-40 50 26</div>
          <div style="font-size:12px;color:rgba(255,255,255,.65)">Alternativt nummer</div>
        </div>
      </a>
      <a href="mailto:info@m2team.se" style="display:flex;align-items:center;gap:13px;padding:20px 24px;transition:background .15s" onmouseover="this.style.background='rgba(0,0,0,.08)'" onmouseout="this.style.background='transparent'">
        <div style="width:44px;height:44px;background:rgba(255,255,255,.18);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:20px;height:20px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div>
          <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.1em;text-transform:uppercase;margin-bottom:3px">E-post</div>
          <div style="font-size:17px;font-weight:700;color:#fff">info@m2team.se</div>
          <div style="font-size:12px;color:rgba(255,255,255,.65)">Svar inom 24 timmar</div>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:52px;align-items:start">

      <!-- FORM -->
      <div>
        <div style="margin-bottom:28px">
          <h2 style="margin-bottom:6px">Begär gratis offert</h2>
          <p style="color:var(--steel)">Kostnadsfritt och utan förpliktelser. Vi svarar inom 24 timmar.</p>
        </div>

        <!-- Error box -->
        <div class="form-error-box" id="form-error-box" role="alert"></div>

        <!-- Success state -->
        <div class="form-success" id="form-success" role="status">
          <strong style="display:block;font-size:16px;margin-bottom:8px">✓ Tack för din förfrågan!</strong>
          Vi har tagit emot din offertförfrågan och återkommer med ett fast pris inom 24 timmar.<br>
          Vill du ha svar snabbare? Ring oss på <strong>031-96 88 88</strong>.
        </div>

        <form id="contact-form" action="/send/contact.php" method="POST" novalidate>
          <!-- Honeypot -->
          <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="fname">Förnamn *</label>
              <input class="form-input" type="text" id="fname" name="fname" placeholder="Anna" required autocomplete="given-name">
            </div>
            <div class="form-group">
              <label class="form-label" for="lname">Efternamn</label>
              <input class="form-input" type="text" id="lname" name="lname" placeholder="Svensson" autocomplete="family-name">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="phone">Telefon *</label>
              <input class="form-input" type="tel" id="phone" name="phone" placeholder="031-XXX XX XX" required autocomplete="tel">
            </div>
            <div class="form-group">
              <label class="form-label" for="email">E-post</label>
              <input class="form-input" type="email" id="email" name="email" placeholder="anna@email.se" autocomplete="email">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="service">Tjänst *</label>
              <select class="form-select" id="service" name="service" required>
                <option value="">Välj tjänst...</option>
                <optgroup label="Tak">
                  <option>Takbyte</option>
                  <option>Takrenovering</option>
                  <option>Takmålning</option>
                  <option>Taktvätt</option>
                  <option>Plåtarbeten / Hängrännor</option>
                </optgroup>
                <optgroup label="Fasad">
                  <option>Fasadmålning</option>
                  <option>Fasadrenovering</option>
                  <option>Fasadtvätt</option>
                </optgroup>
                <optgroup label="Övrigt">
                  <option>Markarbete / Stenläggning</option>
                  <option>Klottersanering</option>
                  <option>Fastighetsservice</option>
                </optgroup>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="city">Stad / adress *</label>
              <input class="form-input" type="text" id="city" name="city" placeholder="T.ex. Göteborg, Kungsbacka..." required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="area">Ungefärlig yta (valfritt)</label>
            <input class="form-input" type="text" id="area" name="area" placeholder="T.ex. 150 m² tak, 200 m² fasad...">
          </div>

          <div class="form-group">
            <label class="form-label" for="message">Beskriv projektet</label>
            <textarea class="form-textarea" id="message" name="message" placeholder="Nuvarande skick, material, önskemål..."></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Bilder (valfritt)</label>
            <div class="upload-zone">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <path d="M21 15l-5-5L5 21"/>
              </svg>
              <p>Klicka för att ladda upp bilder</p>
              <small>PNG, JPG, HEIC – max 10 MB per bild</small>
            </div>
            <input type="file" id="file-input" name="images[]" accept="image/*" multiple style="display:none">
            <p id="upload-status" style="font-size:12.5px;margin-top:6px;min-height:16px"></p>
          </div>

          <div class="form-row" style="margin-bottom:20px">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label" for="calltime">Bästa tid att ringa</label>
              <select class="form-select" id="calltime" name="calltime">
                <option>Spelar ingen roll</option>
                <option>Förmiddag (08–12)</option>
                <option>Eftermiddag (12–17)</option>
                <option>Sen eftermiddag (17–19)</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label" for="source">Hur hörde du om oss?</label>
              <select class="form-select" id="source" name="source">
                <option>Välj...</option>
                <option>Google-sökning</option>
                <option>Rekommendation</option>
                <option>Sociala medier</option>
                <option>Google Maps</option>
                <option>Annat</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn--copper btn--lg" style="width:100%;justify-content:center">
            <span class="btn-text">Skicka offertförfrågan – gratis</span>
            <div class="spinner" style="display:none"></div>
          </button>

          <p style="font-size:12px;color:var(--steel-lt);margin-top:12px;display:flex;align-items:flex-start;gap:6px;line-height:1.55">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--copper);flex-shrink:0;margin-top:2px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Din information är säker och delas aldrig med tredje part.
          </p>
        </form>
      </div>

      <!-- SIDEBAR -->
      <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:90px">
        <!-- Address card -->
        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);padding:22px">
          <h4 style="margin-bottom:14px">Besök oss</h4>
          <?php
          $info = [
            ['icon'=>'<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>','main'=>'Lillhagsvägen 88','sub'=>'442 43 Hisings Backa, Göteborg','href'=>'https://maps.google.com/?q=Lillhagsvagen+88+Hisings+Backa'],
            ['icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>','main'=>'Mån–Fre 07:00–18:00','sub'=>'Jour för akuta ärenden','href'=>null],
          ];
          foreach ($info as $i): ?>
          <?php if($i['href']): ?><a href="<?= $i['href'] ?>" target="_blank" rel="noopener" <?php else: ?><div <?php endif; ?> style="display:flex;align-items:flex-start;gap:11px;margin-bottom:13px;color:inherit">
            <div style="width:36px;height:36px;background:var(--sand-lt);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--copper)"><?= $i['icon'] ?></svg>
            </div>
            <div>
              <div style="font-weight:600;font-size:14px;color:var(--coal)"><?= $i['main'] ?></div>
              <div style="font-size:12.5px;color:var(--steel-lt)"><?= $i['sub'] ?></div>
            </div>
          <?php if($i['href']): ?></a><?php else: ?></div><?php endif; ?>
          <?php endforeach; ?>
        </div>

        <!-- Social -->
        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);padding:22px">
          <h4 style="margin-bottom:14px">Följ oss</h4>
          <div style="display:flex;flex-direction:column;gap:10px">
            <a href="<?= $instagram ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:11px;background:var(--sand-lt);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--copper)"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
              <div><div style="font-weight:600;font-size:14px">Instagram</div><div style="font-size:12px;color:var(--steel-lt)">@m2byggteam</div></div>
            </a>
            <a href="<?= $facebook ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:11px;background:var(--sand-lt);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--coal);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)'" onmouseout="this.style.borderColor='var(--border)'">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--copper)"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
              <div><div style="font-weight:600;font-size:14px">Facebook</div><div style="font-size:12px;color:var(--steel-lt)">M2 Bygg Team</div></div>
            </a>
          </div>
        </div>

        <!-- Trust box -->
        <div style="background:rgba(181,113,42,.08);border:1px solid rgba(181,113,42,.2);border-radius:var(--r-lg);padding:20px">
          <div style="font-size:11px;font-weight:700;color:var(--copper);letter-spacing:.12em;text-transform:uppercase;margin-bottom:10px">Fast pris – alltid</div>
          <p style="font-size:13.5px;color:var(--steel);line-height:1.65">Det pris du får i offerten är det du betalar. Inga tillägg, inga överraskningar. Prisgaranti ingår i varje offert.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
