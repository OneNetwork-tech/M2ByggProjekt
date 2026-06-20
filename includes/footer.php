<?php $year = date('Y'); ?>
<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer__grid">

      <!-- Brand -->
      <div>
        <div class="footer__logo">
          <span class="footer__logo-mark">m2</span>
          <div class="footer__logo-sub"><span>Bygg</span><span>Team</span></div>
        </div>
        <p class="footer__desc">Professionella tak- och fasadarbeten i Göteborg och Västsverige. Fast pris, ROT-avdrag och 5 år garanti på allt vi utför.</p>
        <div class="footer__social">
          <a href="<?= htmlspecialchars($instagram ?? '#') ?>" target="_blank" rel="noopener" aria-label="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
          </a>
          <a href="<?= htmlspecialchars($facebook ?? '#') ?>" target="_blank" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          </a>
          <a href="tel:<?= $phone1_raw ?? '031968888' ?>" aria-label="Ring oss">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          </a>
        </div>
      </div>

      <!-- Tjänster -->
      <div>
        <div class="footer__heading">Tjänster</div>
        <ul>
          <?php foreach([['Takbyte','/tjanster/takbyte'],['Takrenovering','/tjanster/takrenovering'],['Takmålning','/tjanster/takmalning'],['Taktvätt','/tjanster/taktvatt'],['Fasadmålning','/tjanster/fasadmalning'],['Fasadrenovering','/tjanster/fasadrenovering'],['Klä in fasad','/tjanster/kladinfasad'],['Balkongmålning','/tjanster/balkongmalning'],['Plåtarbeten','/tjanster/platarbeten'],['Stenläggning','/tjanster/stenlaggning']] as $l): ?>
          <li><a href="<?= htmlspecialchars($l[1]) ?>"><?= htmlspecialchars($l[0]) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Företaget -->
      <div>
        <div class="footer__heading">Företaget</div>
        <ul>
          <?php foreach([['Om oss','/om-oss'],['För fastigheter','/fastighet'],['Projekt','/projekt'],['Prisguide 2025','/prisguide'],['Blogg & tips','/blogg'],['Vanliga frågor','/faq'],['Bli partner','/bli-partner'],['Integritetspolicy','/integritetspolicy']] as $l): ?>
          <li><a href="<?= htmlspecialchars($l[1]) ?>"><?= htmlspecialchars($l[0]) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Kontakt -->
      <div>
        <div class="footer__heading">Kontakt</div>
        <div class="footer__contact-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
          <span><a href="tel:<?= $phone1_raw ?? '031968888' ?>"><?= htmlspecialchars($phone1 ?? '031-96 88 88') ?></a><br><a href="tel:<?= $phone2_raw ?? '0732405026' ?>"><?= htmlspecialchars($phone2 ?? '0732-40 50 26') ?></a></span>
        </div>
        <div class="footer__contact-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <a href="mailto:<?= htmlspecialchars($email_addr ?? 'info@m2team.se') ?>"><?= htmlspecialchars($email_addr ?? 'info@m2team.se') ?></a>
        </div>
        <div class="footer__contact-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span>Lillhagsvägen 88<br>442 43 Hisings Backa</span>
        </div>
        <div class="footer__contact-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
          Mån–Fre 07:00–18:00
        </div>
        <div style="margin-top:20px">
          <a href="/offert" class="btn btn--primary btn--sm">Få offert →</a>
        </div>
      </div>

    </div>
    <div class="footer__bottom">
      <span>© <?= $year ?> M2 Bygg Team AB. Alla rättigheter förbehållna.</span>
      <div style="display:flex;gap:20px;flex-wrap:wrap">
        <a href="/integritetspolicy">Integritetspolicy</a>
        <button type="button" onclick="openCookieSettings()" class="footer__cookie-btn">Cookie-inställningar</button>
        <a href="/faq">FAQ</a>
        <a href="/sitemap.xml">Sitemap</a>
        <a href="/crm/login.php" style="opacity:.35">CRM</a>
      </div>
    </div>
  </div>
</footer>

<script src="/js/main.js" defer></script>
<?= $extra_js ?? '' ?>
<?php require_once __DIR__ . '/cookies.php'; ?>
</body>
</html>