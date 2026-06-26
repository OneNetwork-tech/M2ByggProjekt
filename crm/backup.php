<?php
/**
 * CRM — Backups (SQLite db + portal-uploads)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/backup.php';
$me = require_role(['super_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = backup_create();
        if ($name) {
            audit('backup_create', 'system', 0, $name);
            flash('Backup skapad: ' . $name);
        } else {
            flash('Backup misslyckades. Kontrollera serverloggarna.', 'error');
        }
    } elseif ($action === 'delete') {
        $name = $_POST['name'] ?? '';
        if (backup_delete($name)) {
            audit('backup_delete', 'system', 0, $name);
            flash('Backup borttagen.');
        }
    } elseif ($action === 'retention') {
        $keep = max(1, (int)($_POST['keep'] ?? 14));
        $deleted = backup_apply_retention($keep);
        flash("$deleted gamla backuper rensades bort (behåller de $keep senaste).");
    }
    header('Location: backup.php'); exit;
}

// Download
if (isset($_GET['download'])) {
    $name = $_GET['download'];
    $path = backup_path($name);
    if (!file_exists($path) || backup_is_folder($name)) {
        http_response_code(404); die('Backupen kan inte laddas ner direkt (mapp-baserad backup). Använd FTP/filhanteraren för att hämta filerna manuellt.');
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

$backups = backup_list();
$totalSize = array_sum(array_column($backups, 'size'));

$crm_title = 'Backup';
$crm_page  = 'backup';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Backup</h1>
    <div class="topbar__sub">Säkerhetskopiering av databas och uppladdade filer</div>
  </div>
  <div class="topbar__actions">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="create">
      <button class="btn btn--primary">+ Skapa backup nu</button>
    </form>
  </div>
</div>

<?php flash(); ?>

<?php if (DB_DRIVER === 'mysql'): ?>
<div class="alert alert-danger" style="margin-bottom:16px">
  <strong>Obs:</strong> Databasen körs på MySQL — denna funktion säkerhetskopierar endast uppladdade filer, inte databasen.
  Säkerhetskopiera MySQL separat via cPanel &gt; Backup Wizard, eller schemalägg <code>mysqldump</code>.
</div>
<?php endif; ?>

<div class="card card--pad" style="margin-bottom:20px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
    <h3 style="font-size:14.5px;margin:0">Backuper (<?= count($backups) ?>)</h3>
    <span style="font-size:12px;color:var(--gray)">Totalt: <?= number_format($totalSize / 1048576, 1) ?> MB</span>
  </div>
</div>

<div class="card" style="overflow:hidden">
  <?php if ($backups): ?>
  <table class="data">
    <thead><tr><th>Namn</th><th>Skapad</th><th>Storlek</th><th>Typ</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($backups as $b): ?>
    <tr style="cursor:default">
      <td style="font-size:12.5px;font-family:monospace"><?= e($b['name']) ?></td>
      <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= date('j M Y H:i', $b['mtime']) ?></td>
      <td style="font-size:12.5px"><?= number_format($b['size'] / 1048576, 1) ?> MB</td>
      <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= $b['is_folder'] ? 'Mapp' : (str_ends_with($b['name'], '.zip') ? 'ZIP' : 'TAR.GZ') ?></span></td>
      <td style="display:flex;gap:6px">
        <?php if (!$b['is_folder']): ?>
        <a href="?download=<?= rawurlencode($b['name']) ?>" class="btn btn--ghost btn--sm">Ladda ner</a>
        <?php else: ?>
        <span style="font-size:11px;color:var(--gray-lt)">Mapp i /data/backups/</span>
        <?php endif; ?>
        <form method="post" onsubmit="return confirm('Ta bort denna backup permanent?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="name" value="<?= e($b['name']) ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">Ta bort</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="padding:24px;color:var(--gray);font-size:13px">Inga backuper ännu. Klicka "Skapa backup nu" för att göra den första.</p>
  <?php endif; ?>
</div>

<div class="card card--pad" style="margin-top:20px;max-width:420px">
  <h3 style="font-size:14.5px;margin-bottom:12px">Behåll endast de senaste</h3>
  <form method="post" style="display:flex;gap:10px;align-items:flex-end">
    <?= csrf_field() ?><input type="hidden" name="action" value="retention">
    <div class="fg" style="margin:0;flex:1"><label>Antal att behålla</label><input class="fi" type="number" name="keep" value="14" min="1"></div>
    <button class="btn btn--ghost btn--sm">Rensa äldre</button>
  </form>
</div>

<div class="card card--pad" style="margin-top:20px;background:#F9FAFB">
  <h3 style="font-size:14px;margin-bottom:8px">Schemalägg automatiska backuper (cPanel cron)</h3>
  <p style="font-size:12.5px;color:var(--gray);margin-bottom:8px">Lägg till en cron-job i cPanel &gt; Cron Jobs som körs dagligen, t.ex. kl 03:00:</p>
  <code style="display:block;background:#fff;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-size:12px;word-break:break-all">0 3 * * * php <?= dirname(__DIR__) ?>/crm/cron-backup.php</code>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
