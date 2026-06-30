<?php
/**
 * CRM — Kundrecensioner (customer reviews left in the portal, shown on the public homepage)
 * Admin can hide a review from the public site without deleting it from the system.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_visible') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE reviews SET visible = 1 - visible WHERE id=?")->execute([$id]);
        audit('review_toggle_visible', 'review', $id);
        flash('Synlighet ändrad.');
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$id]);
        audit('review_delete', 'review', $id);
        flash('Recension borttagen.');
    } elseif ($action === 'reply') {
        $id   = (int)($_POST['id'] ?? 0);
        $body = trim($_POST['reply_body'] ?? '');
        if ($body === '') {
            $pdo->prepare("UPDATE reviews SET reply_body = NULL, reply_at = NULL WHERE id=?")->execute([$id]);
            flash('Svaret togs bort.');
        } else {
            $pdo->prepare("UPDATE reviews SET reply_body = ?, reply_at = " . now_expr() . " WHERE id=?")->execute([$body, $id]);
            audit('review_reply', 'review', $id);
            flash('Svar publicerat.');
        }
    }
    header('Location: recensioner.php'); exit;
}

$reviews = $pdo->query("
    SELECT r.*, c.name AS customer_name, c.city AS customer_city, p.title AS project_title
    FROM reviews r
    JOIN customers c ON c.id = r.customer_id
    JOIN projects p ON p.id = r.project_id
    ORDER BY r.created_at DESC
")->fetchAll();

$crm_title = 'Kundrecensioner';
$crm_page  = 'recensioner';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Kundrecensioner</h1>
    <div class="topbar__sub"><?= count($reviews) ?> recensioner · synliga visas i "Vad kunderna säger" på startsidan</div>
  </div>
</div>

<?php flash(); ?>

<div class="card" style="overflow:hidden">
  <table class="data">
    <thead><tr><th>Recension</th><th>Betyg</th><th>Kund</th><th>Projekt</th><th>Datum</th><th>Svar</th><th>Synlig på webbplatsen</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($reviews as $r): ?>
    <tr>
      <td style="font-size:13px;max-width:320px"><?= e(mb_strimwidth($r['body'], 0, 140, '…')) ?></td>
      <td style="color:#D97706;white-space:nowrap"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></td>
      <td style="font-size:12.5px"><?= e($r['customer_name']) ?><br><span style="color:var(--gray)"><?= e($r['customer_city'] ?: '') ?></span></td>
      <td style="font-size:12.5px"><?= e($r['project_title']) ?></td>
      <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= e(substr($r['created_at'],0,10)) ?></td>
      <td>
        <a href="#" onclick="event.preventDefault();openReplyModal(<?= (int)$r['id'] ?>, <?= htmlspecialchars(json_encode($r['reply_body']), ENT_QUOTES) ?>)" style="font-size:12px">
          <?= $r['reply_body'] ? 'Redigera svar' : '+ Svara' ?>
        </a>
      </td>
      <td>
        <form method="post" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="toggle_visible"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="badge-<?= $r['visible'] ? 'success' : 'danger' ?>" style="padding:3px 9px;border-radius:20px;font-size:11.5px;border:none;cursor:pointer"><?= $r['visible'] ? 'Visas på webbplatsen' : 'Dold från webbplatsen' ?></button>
        </form>
      </td>
      <td>
        <form method="post" onsubmit="return confirm('Ta bort recensionen permanent?')" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">Ta bort</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$reviews): ?><tr><td colspan="8" style="padding:24px;color:var(--gray);font-size:13px">Inga recensioner ännu.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<p style="font-size:12px;color:var(--gray);margin-top:14px">
  Att dölja en recension tar bara bort den från webbplatsen — den finns kvar i systemet och kan visas igen när du vill.
</p>

<div class="modal-bg" id="replyModal">
  <div class="modal">
    <h3>Svara på recension</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="reply"><input type="hidden" name="id" id="rp_id" value="">
      <div class="fg"><label>Svar (visas publikt under recensionen)</label>
        <textarea class="fi" name="reply_body" id="rp_body" rows="4" placeholder="Tack för din recension!"></textarea>
      </div>
      <p style="font-size:11.5px;color:var(--gray);margin-top:-4px">Lämna tomt och spara för att ta bort ett befintligt svar.</p>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('replyModal')">Avbryt</button>
        <button class="btn btn--primary">Spara svar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReplyModal(id, replyBody) {
  document.getElementById('rp_id').value = id;
  document.getElementById('rp_body').value = replyBody || '';
  openModal('replyModal');
}
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
