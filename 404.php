<?php
http_response_code(404);
$page_title = '404 – Sidan hittades inte';
$active_page = '';
require_once __DIR__ . '/includes/header.php';
?>
<section style="min-height:calc(100vh - 68px);display:flex;align-items:center;justify-content:center;text-align:center;padding:60px 24px;background:var(--sand-lt);position:relative;overflow:hidden">
  <div style="position:absolute;top:-120px;right:-100px;width:500px;height:500px;background:radial-gradient(circle,rgba(181,113,42,.07) 0%,transparent 65%);pointer-events:none"></div>
  <div style="position:relative;z-index:1;max-width:520px">
    <div style="font-family:var(--font-display);font-size:clamp(5rem,18vw,10rem);font-weight:700;letter-spacing:-0.05em;line-height:1;color:var(--coal);margin-bottom:12px">
      4<span style="color:var(--copper)">0</span>4
    </div>
    <h1 style="font-size:1.8rem;margin-bottom:12px">Sidan hittades inte</h1>
    <p style="color:var(--steel);margin-bottom:36px;line-height:1.65">Den sida du letar efter finns inte eller har flyttats. Gå tillbaka till startsidan eller kontakta oss direkt.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="/" class="btn btn--primary btn--lg">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Tillbaka till startsidan
      </a>
      <a href="tel:031968888" class="btn btn--outline btn--lg">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.3 7.74A16 16 0 0016.3 17.7l1.1-1.1a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0124 18z"/></svg>
        031-96 88 88
      </a>
    </div>
    <div style="margin-top:32px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
      <?php foreach([['Tak','/tjanster/takbyte'],['Fasad','/tjanster/fasadmalning'],['Kontakt','/kontakt'],['Priser','/prisguide']] as $l): ?>
      <a href="<?= $l[1] ?>" style="background:var(--white);border:1px solid var(--border);border-radius:980px;padding:8px 16px;font-size:13.5px;color:var(--steel);transition:all .15s" onmouseover="this.style.borderColor='var(--copper)';this.style.color='var(--coal)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--steel)'"><?= $l[0] ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
