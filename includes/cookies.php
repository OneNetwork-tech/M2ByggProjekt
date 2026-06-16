<?php /* Cookie consent banner — Swedish LEK + GDPR compliant */ ?>
<div id="cookie-banner" class="cookie-banner" role="dialog" aria-modal="true" aria-labelledby="cookie-title" aria-describedby="cookie-desc" hidden>
  <div class="cookie-banner__inner">
    <div class="cookie-banner__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
    </div>
    <div class="cookie-banner__body">
      <p class="cookie-banner__title" id="cookie-title">Vi använder cookies</p>
      <p class="cookie-banner__desc" id="cookie-desc">
        Vi använder nödvändiga cookies för att webbplatsen ska fungera korrekt, samt analytiska och marknadsföringscookies för att förbättra din upplevelse och visa relevanta annonser. Du kan välja vilka cookies du godkänner. Läs mer i vår <a href="/integritetspolicy" class="cookie-banner__link">integritetspolicy</a>.
      </p>
    </div>
    <div class="cookie-banner__actions">
      <button id="cookie-deny"   class="btn btn--sm cookie-banner__deny"   type="button">Avvisa alla</button>
      <button id="cookie-accept" class="btn btn--sm cookie-banner__accept" type="button">Acceptera alla</button>
    </div>
    <button id="cookie-close" class="cookie-banner__close" type="button" aria-label="Stäng">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
</div>

<script>
(function () {
  var CONSENT_KEY = 'm2_cookie_consent';
  var CONSENT_TTL = 365; // days

  function getConsent() {
    try { return JSON.parse(localStorage.getItem(CONSENT_KEY)); } catch(e) { return null; }
  }

  function setConsent(value) {
    var exp = new Date();
    exp.setDate(exp.getDate() + CONSENT_TTL);
    localStorage.setItem(CONSENT_KEY, JSON.stringify({ value: value, expires: exp.toISOString() }));
    // Set cookie so server-side PHP can also read the consent
    document.cookie = CONSENT_KEY + '=' + value + '; expires=' + exp.toUTCString() + '; path=/; SameSite=Lax';
  }

  function isExpired(consent) {
    return !consent || (consent.expires && new Date(consent.expires) < new Date());
  }

  function hideBanner() {
    var b = document.getElementById('cookie-banner');
    if (b) { b.setAttribute('hidden', ''); b.removeAttribute('aria-modal'); }
  }

  function showBanner() {
    var b = document.getElementById('cookie-banner');
    if (!b) return;
    b.removeAttribute('hidden');
    // Move focus to first button for keyboard accessibility
    setTimeout(function() {
      var btn = b.querySelector('button');
      if (btn) btn.focus();
    }, 100);
  }

  function applyConsent(value) {
    if (value === 'accepted') {
      // Load analytics / marketing scripts here when consent is given
      // Example: loadGoogleAnalytics();
      document.dispatchEvent(new CustomEvent('cookieConsent', { detail: { accepted: true } }));
    }
  }

  function init() {
    var consent = getConsent();
    if (!consent || isExpired(consent)) {
      showBanner();
    } else {
      applyConsent(consent.value);
    }

    var btnAccept = document.getElementById('cookie-accept');
    var btnDeny   = document.getElementById('cookie-deny');
    var btnClose  = document.getElementById('cookie-close');

    if (btnAccept) btnAccept.addEventListener('click', function() {
      setConsent('accepted');
      applyConsent('accepted');
      hideBanner();
    });

    if (btnDeny) btnDeny.addEventListener('click', function() {
      setConsent('denied');
      hideBanner();
    });

    if (btnClose) btnClose.addEventListener('click', function() {
      // Closing without choosing = treat as deny (Swedish DPA guidance)
      setConsent('denied');
      hideBanner();
    });

    // Escape key closes as deny
    document.addEventListener('keydown', function(e) {
      var b = document.getElementById('cookie-banner');
      if (e.key === 'Escape' && b && !b.hasAttribute('hidden')) {
        setConsent('denied');
        hideBanner();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Allow re-opening the banner from footer link
  window.openCookieSettings = function() {
    localStorage.removeItem(CONSENT_KEY);
    document.cookie = CONSENT_KEY + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    showBanner();
  };
})();
</script>
