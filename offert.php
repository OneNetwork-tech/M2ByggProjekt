<?php
$page_title       = 'Gratis Offert – Fast Pris inom 24h';
$page_description = 'Begär gratis offert från M2 Bygg Team AB. Fast pris på tak, fasad och markarbeten i Göteborg. ROT-avdrag. Svar inom 24h. Ring 031-96 88 88.';
$active_page      = 'offert';
require_once __DIR__ . '/includes/header.php';
?>

<section style="min-height:calc(100vh - 68px);display:grid;grid-template-columns:1fr 520px">

  <!-- LEFT – Trust panel -->
  <div style="background:var(--coal);padding:64px 52px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden">
    <div style="position:absolute;top:-100px;left:-80px;width:500px;height:500px;background:radial-gradient(circle,rgba(181,113,42,.1) 0%,transparent 65%);pointer-events:none"></div>
    <div style="position:relative;z-index:1;max-width:480px">
      <p class="eyebrow animate-in" style="color:var(--copper-lt);margin-bottom:20px">Svar inom 24 timmar</p>
      <h1 class="animate-in delay-1" style="font-size:clamp(2.2rem,4vw,3.4rem);margin-bottom:18px">Gratis offert.<br>Fast pris. Alltid.</h1>
      <p class="animate-in delay-2" style="font-size:1.05rem;margin-bottom:36px">Fyll i formuläret – vi återkommer med ett skriftligt fast pris inom 24 timmar. Kostnadsfritt och utan förpliktelser.</p>

      <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:40px" class="animate-in delay-3">
        <?php
        $trust = [
          ['<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 5h-2v6h2V7zm0 8h-2v2h2v-2z" fill="currentColor"/>','Fast pris – alltid','Prisgaranti ingår i varje offert'],
          ['<path d="M9 14l6-6M9.5 8.5a.5.5 0 110-1 .5.5 0 010 1zm5 5a.5.5 0 110-1 .5.5 0 010 1z"/><rect x="3" y="3" width="18" height="18" rx="3"/>','ROT-avdrag upp till 50%','Vi hanterar ansökan till Skatteverket'],
          ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','5 år garanti','På allt arbete vi utför'],
          ['<path d="M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3"/>','4,9/5 på Google','Baserat på 1 000+ recensioner'],
        ];
        foreach ($trust as $t): ?>
        <div style="display:flex;align-items:center;gap:13px">
          <div style="width:42px;height:42px;background:rgba(181,113,42,.15);border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:19px;height:19px;color:var(--copper-lt)"><?= $t[0] ?></svg>
          </div>
          <div>
            <div style="font-family:var(--font-display);font-weight:600;font-size:14.5px;color:var(--sand-lt);margin-bottom:2px"><?= $t[1] ?></div>
            <div style="font-size:12.5px;color:rgba(245,245,247,.45)"><?= $t[2] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Phone -->
      <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:28px">
        <div style="font-size:12px;font-weight:600;color:rgba(245,245,247,.35);letter-spacing:.09em;text-transform:uppercase;margin-bottom:12px">Eller ring oss direkt</div>
        <a href="tel:031968888" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;color:var(--sand-lt);transition:color .15s" onmouseover="this.style.color='var(--copper-lt)'" onmouseout="this.style.color='var(--sand-lt)'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--copper);flex-shrink:0"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          <span style="font-family:var(--font-display);font-weight:600;font-size:20px;letter-spacing:-0.02em">031-96 88 88</span>
        </a>
        <a href="tel:0732405026" style="display:flex;align-items:center;gap:10px;color:rgba(245,245,247,.45);font-size:15px;transition:color .15s" onmouseover="this.style.color='var(--sand-lt)'" onmouseout="this.style.color='rgba(245,245,247,.45)'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--copper);flex-shrink:0"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          0732-40 50 26
        </a>
        <div style="font-size:12px;color:rgba(245,245,247,.3);margin-top:8px">Mån–Fre 07:00–18:00</div>
      </div>
    </div>
  </div>

  <!-- RIGHT – Form -->
  <div style="background:var(--sand-lt);padding:60px 48px;overflow-y:auto">
    <div style="max-width:420px">
      <h2 style="margin-bottom:6px">Berätta om ditt projekt</h2>
      <p style="color:var(--steel);margin-bottom:28px;font-size:15px">Vi återkommer med ett fast pris inom 24 timmar.</p>

      <div class="form-error-box" id="form-error-box" role="alert"></div>
      <div class="form-success" id="form-success" role="status">
        <strong style="display:block;font-size:16px;margin-bottom:7px">✓ Tack! Vi hör av oss snart.</strong>
        Din förfrågan är mottagen. Vi återkommer med fast pris inom 24 timmar.<br>
        Snabbare svar? Ring <strong>031-96 88 88</strong>.
      </div>

      <form id="contact-form" action="/send/contact.php" method="POST" novalidate>
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

        <div class="form-group">
          <label class="form-label" for="phone">Telefon *</label>
          <input class="form-input" type="tel" id="phone" name="phone" placeholder="031-XXX XX XX" required autocomplete="tel">
        </div>

        <div class="form-group">
          <label class="form-label" for="email">E-post</label>
          <input class="form-input" type="email" id="email" name="email" placeholder="anna@email.se" autocomplete="email">
        </div>

        <div class="form-group">
          <label class="form-label" for="service">Vad behöver du hjälp med? *</label>
          <select class="form-select" id="service" name="service" required>
            <option value="">Välj tjänst...</option>
            <optgroup label="Tak">
              <option>Takbyte</option><option>Takrenovering</option>
              <option>Takmålning</option><option>Taktvätt</option>
              <option>Plåtarbeten / Hängrännor</option>
            </optgroup>
            <optgroup label="Fasad">
              <option>Fasadmålning</option><option>Fasadrenovering</option><option>Fasadtvätt</option>
            </optgroup>
            <optgroup label="Övrigt">
              <option>Markarbete / Stenläggning</option><option>Klottersanering</option>
            </optgroup>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="city">Stad / adress *</label>
          <input class="form-input" type="text" id="city" name="city" placeholder="T.ex. Göteborg, Kungsbacka..." required>
        </div>

        <div class="form-group">
          <label class="form-label" for="area">Ungefärlig yta</label>
          <input class="form-input" type="text" id="area" name="area" placeholder="T.ex. 150 m² tak...">
        </div>

        <div class="form-group">
          <label class="form-label" for="message">Beskriv projektet</label>
          <textarea class="form-textarea" id="message" name="message" style="min-height:100px" placeholder="Nuvarande skick, material, önskemål..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Bilder (valfritt)</label>
          <div class="upload-zone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            <p>Klicka för att ladda upp</p>
            <small>PNG, JPG – max 10 MB</small>
          </div>
          <input type="file" id="file-input" name="images[]" accept="image/*" multiple style="display:none">
          <p id="upload-status" style="font-size:12px;margin-top:5px;min-height:16px"></p>
        </div>

        <button type="submit" class="btn btn--copper btn--lg" style="width:100%;justify-content:center">
          <span class="btn-text">Skicka offertförfrågan – gratis</span>
          <div class="spinner" style="display:none"></div>
        </button>

        <p style="font-size:12px;color:var(--steel-lt);margin-top:10px;line-height:1.5">
          Din information delas aldrig med tredje part. Vi kontaktar dig enbart angående din förfrågan.
        </p>
      </form>
    </div>
  </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
