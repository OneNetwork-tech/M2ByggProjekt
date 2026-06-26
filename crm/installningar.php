<?php
$crm_title = 'Inställningar';
$crm_page  = 'installningar';
require_once __DIR__ . '/includes/crm-header.php';
require_role([]); // super_admin only
$pdo = db();

// Audit log
$logs = $pdo->query("SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 60")->fetchAll();

// DB stats
$stats = [];
foreach (['leads','customers','quotes','projects','invoices','suppliers','timeline','users'] as $t) {
    $stats[$t] = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}

// Notification send log
$notifLog = $pdo->query("SELECT * FROM notifications_log ORDER BY created_at DESC LIMIT 40")->fetchAll();
$notifFailCount = $pdo->query("SELECT COUNT(*) FROM notifications_log WHERE status='failed'")->fetchColumn();
?>

<div class="topbar">
  <div>
    <h1>Inställningar</h1>
    <div class="topbar__sub">System, granskningslogg och databasinfo</div>
  </div>
</div>

<?php flash(); ?>

<div class="detail-grid">
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:14px">Granskningslogg (Audit Log)</h3>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Tid</th><th>Användare</th><th>Händelse</th><th>Objekt</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
          <tr style="cursor:default">
            <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= dt($l['created_at'], 'j M H:i') ?></td>
            <td style="font-size:12.5px"><?= e($l['user_name'] ?: 'System') ?></td>
            <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= e($l['action']) ?></span></td>
            <td style="font-size:12px;color:var(--gray)"><?= e($l['entity_type']) ?><?= $l['entity_id'] ? ' #' . $l['entity_id'] : '' ?> <?= e($l['detail']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card card--pad" style="margin-top:16px">
      <h3 style="font-size:14.5px;margin-bottom:14px">
        E-post/SMS-utskick
        <?php if ($notifFailCount): ?><span class="badge badge-warning" style="font-size:11px;margin-left:6px"><?= $notifFailCount ?> misslyckade</span><?php endif; ?>
      </h3>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Tid</th><th>Kanal</th><th>Mottagare</th><th>Ämne</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$notifLog): ?><tr><td colspan="5" style="color:var(--gray);font-size:13px">Inga utskick ännu.</td></tr><?php endif; ?>
          <?php foreach ($notifLog as $n): ?>
          <tr style="cursor:default">
            <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= dt($n['created_at'], 'j M H:i') ?></td>
            <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= e($n['channel']) ?></span></td>
            <td style="font-size:12.5px"><?= e($n['recipient']) ?></td>
            <td style="font-size:12px;color:var(--gray);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($n['subject'] ?? '') ?></td>
            <td>
              <?php if ($n['status'] === 'sent'): ?><span class="badge badge-success">Skickat</span>
              <?php else: ?><span class="badge badge-danger" title="<?= e($n['error'] ?? '') ?>">Misslyckades</span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Integrationer</h3>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">SMTP e-post</span><strong style="color:<?= defined('SMTP_PASS_OVERRIDE') ? 'var(--green)' : 'var(--amber)' ?>"><?= defined('SMTP_PASS_OVERRIDE') ? 'Konfigurerad' : 'Standardlösenord' ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">SMS (46elks)</span><strong style="color:<?= defined('SMS_PROVIDER_USER') ? 'var(--green)' : 'var(--gray-lt)' ?>"><?= defined('SMS_PROVIDER_USER') ? 'Aktiverad' : 'Ej konfigurerad' ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Stripe-betalning</span><strong style="color:<?= defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '' ? 'var(--green)' : 'var(--gray-lt)' ?>"><?= defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '' ? 'Aktiverad' : 'Ej konfigurerad' ?></strong></div>
      </div>
      <p style="font-size:11.5px;color:var(--gray-lt);margin-top:10px">Konfigureras i crm/config.php och send/config.local.php.</p>
    </div>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Systeminfo</h3>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Version</span><strong><?= APP_VERSION ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Databas</span><strong><?= strtoupper(DB_DRIVER) ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">PHP</span><strong><?= PHP_VERSION ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Moms</span><strong><?= VAT_RATE * 100 ?>%</strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">ROT-sats</span><strong><?= ROT_RATE * 100 ?>% (max 50 000 kr)</strong></div>
      </div>
    </div>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Databas</h3>
      <div style="display:flex;flex-direction:column;gap:7px;font-size:13px">
        <?php
        $labels = ['leads'=>'Leads','customers'=>'Kunder','quotes'=>'Offerter','projects'=>'Projekt','invoices'=>'Fakturor','suppliers'=>'Leverantörer','timeline'=>'Tidslinjehändelser','users'=>'Användare'];
        foreach ($stats as $t => $c): ?>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)"><?= $labels[$t] ?></span><strong><?= $c ?></strong></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card card--pad" style="background:#FFFBEB;border-color:#FDE68A">
      <h3 style="font-size:14px;color:var(--amber);margin-bottom:8px">⚠ Säkerhet vid driftsättning</h3>
      <ul style="font-size:12.5px;color:var(--ink-soft);padding-left:16px;line-height:1.8">
        <li>Byt admin-lösenordet (admin@m2team.se / admin123)</li>
        <li>Skydda /data/-mappen via .htaccess (ingår)</li>
        <li>Aktivera HTTPS via cPanel</li>
        <li>Byt till MySQL i config.php för hög volym</li>
      </ul>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
