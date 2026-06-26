<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/crm/includes/gdpr.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_erasure') {
    $existing = db()->prepare("SELECT id FROM gdpr_requests WHERE entity_type='supplier' AND entity_id=? AND status='pending'");
    $existing->execute([$sid]);
    if (!$existing->fetch()) {
        db()->prepare("INSERT INTO gdpr_requests (type, entity_type, entity_id, requested_by) VALUES ('erasure','supplier',?,'self')")
            ->execute([$sid]);
        notify_role('project', 'GDPR-raderingsbegäran', $su['company'] . ' har begärt radering av sina personuppgifter.', '/crm/gdpr.php');
    }
    $msg = 'Din begäran om radering har skickats till oss. Vi kontaktar dig inom 30 dagar enligt GDPR. Observera att uppdrags- och betalningsuppgifter måste sparas i 7 år enligt bokföringslagen.';
}

$pendingErasure = db()->prepare("SELECT * FROM gdpr_requests WHERE entity_type='supplier' AND entity_id=? AND status='pending' AND type='erasure'");
$pendingErasure->execute([$sid]);
$pendingErasure = $pendingErasure->fetch();

if (isset($_GET['export'])) {
    $data = gdpr_export_supplier($sid);
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="mina-uppgifter.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

supp_head('Inställningar', $su);
supp_nav('/installningar.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Inställningar & dataskydd</h1><p>Hantera era personuppgifter enligt GDPR.</p></div>

  <?php if ($msg): ?><div class="alert alert--success" style="margin-bottom:20px"><?= e($msg) ?></div><?php endif; ?>

  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><h3>Exportera era uppgifter</h3></div>
    <div style="padding:20px">
      <p style="font-size:.875rem;color:var(--steel);margin-bottom:14px">Ladda ner allt vi har sparat — kontaktuppgifter, uppdrag, tidrapporter och dokument — som en JSON-fil.</p>
      <a href="?export=1" class="btn btn--primary btn--sm">⬇ Ladda ner era uppgifter</a>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Radera vårt konto</h3></div>
    <div style="padding:20px">
      <?php if ($pendingErasure): ?>
      <div class="alert alert--info">Er raderingsbegäran inkom <?= e(substr($pendingErasure['requested_at'],0,10)) ?> och väntar på behandling.</div>
      <?php else: ?>
      <p style="font-size:.875rem;color:var(--steel);margin-bottom:14px">
        Ni kan begära att vi raderar era personuppgifter. Observera att betalnings- och tidrapportunderlag måste sparas i 7 år enligt svensk bokföringslag — dessa anonymiseras istället för att raderas helt.
      </p>
      <form method="post" onsubmit="return confirm('Begär radering av era personuppgifter?')">
        <input type="hidden" name="action" value="request_erasure">
        <button type="submit" class="btn btn--danger btn--sm">Begär radering</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php supp_foot(); ?>
