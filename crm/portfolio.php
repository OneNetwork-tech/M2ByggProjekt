<?php
/**
 * CRM — Projektportfolio (public "Verkliga resultat" showcase, /projekt)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

$categories = ['tak' => 'Tak', 'fasad' => 'Fasad', 'plat' => 'Plåt', 'mark' => 'Mark'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $linkedProjectId = (int)($_POST['linked_project_id'] ?? 0) ?: null;
        $uploadError = null;
        $uploadedImage = handle_public_image_upload('image_file', 'portfolio', $uploadError);
        $image = $uploadedImage ?? trim($_POST['image'] ?? '');

        if ($uploadError) {
            flash($uploadError, 'error');
        } elseif ($title === '' || $image === '') {
            flash('Titel och bild krävs.', 'error');
        } else {
            $fields = [
                $title, trim($_POST['subtitle'] ?? ''), $_POST['category'] ?? 'tak', $image,
                (int)($_POST['height'] ?? 280), (int)($_POST['sort_order'] ?? 0), isset($_POST['visible']) ? 1 : 0, $linkedProjectId,
            ];
            if ($id) {
                $pdo->prepare("UPDATE portfolio_projects SET title=?, subtitle=?, category=?, image=?, height=?, sort_order=?, visible=?, linked_project_id=? WHERE id=?")
                    ->execute([...$fields, $id]);
                audit('portfolio_update', 'portfolio_project', $id);
                flash('Projekt uppdaterat.');
            } else {
                $pdo->prepare("INSERT INTO portfolio_projects (title, subtitle, category, image, height, sort_order, visible, linked_project_id) VALUES (?,?,?,?,?,?,?,?)")
                    ->execute($fields);
                audit('portfolio_create', 'portfolio_project', $pdo->lastInsertId());
                flash('Projekt tillagt.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM portfolio_projects WHERE id=?")->execute([$id]);
        audit('portfolio_delete', 'portfolio_project', $id);
        flash('Projekt borttaget.');
    } elseif ($action === 'toggle_visible') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE portfolio_projects SET visible = 1 - visible WHERE id=?")->execute([$id]);
        flash('Synlighet ändrad.');
    }
    header('Location: portfolio.php'); exit;
}

$portfolioItems = $pdo->query(
    "SELECT pp.*, p.title AS linked_project_title, r.rating AS review_rating
     FROM portfolio_projects pp
     LEFT JOIN projects p ON p.id = pp.linked_project_id
     LEFT JOIN reviews r ON r.project_id = pp.linked_project_id AND r.visible = 1
     ORDER BY pp.sort_order, pp.created_at DESC"
)->fetchAll();
$crmProjects = $pdo->query("SELECT id, title FROM projects ORDER BY title")->fetchAll();

$crm_title = 'Projektportfolio';
$crm_page  = 'portfolio';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Projektportfolio</h1>
    <div class="topbar__sub"><?= count($portfolioItems) ?> bilder · visas på /projekt på webbplatsen</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" id="newPortfolioBtn" onclick="openModal('portfolioModal')">+ Lägg till projekt</button>
  </div>
</div>

<?php flash(); ?>

<div class="card" style="overflow:hidden">
  <table class="data">
    <thead><tr><th>Bild</th><th>Titel</th><th>Kategori</th><th>Recension</th><th>Ordning</th><th>Synlig</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($portfolioItems as $p): ?>
    <tr>
      <td><img src="<?= e($p['image']) ?>" alt="" style="width:64px;height:44px;object-fit:cover;border-radius:6px;display:block"></td>
      <td>
        <a href="#" onclick="event.preventDefault();editPortfolio(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" style="font-weight:550"><?= e($p['title']) ?></a>
        <div style="font-size:11.5px;color:var(--gray)"><?= e($p['subtitle']) ?></div>
        <?php if ($p['linked_project_title']): ?><div style="font-size:11px;color:var(--gray)">Kopplad till: <?= e($p['linked_project_title']) ?></div><?php endif; ?>
      </td>
      <td style="font-size:12.5px"><?= e($categories[$p['category']] ?? $p['category']) ?></td>
      <td style="font-size:12.5px;color:var(--gold)"><?= $p['review_rating'] ? str_repeat('★', (int)$p['review_rating']) : '<span style="color:var(--gray)">—</span>' ?></td>
      <td style="font-size:12.5px;color:var(--gray)"><?= (int)$p['sort_order'] ?></td>
      <td>
        <form method="post" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="toggle_visible"><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="badge-<?= $p['visible'] ? 'success' : 'danger' ?>" style="padding:3px 9px;border-radius:20px;font-size:11.5px;border:none;cursor:pointer"><?= $p['visible'] ? 'Synlig' : 'Dold' ?></button>
        </form>
      </td>
      <td>
        <form method="post" onsubmit="return confirm('Ta bort permanent?')" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">Ta bort</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$portfolioItems): ?><tr><td colspan="7" style="padding:24px;color:var(--gray);font-size:13px">Inga projekt i portfolion ännu.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal-bg" id="portfolioModal">
  <div class="modal">
    <h3 id="portfolioModalTitle">Lägg till projekt</h3>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="pf_id" value="">
      <div class="fg"><label>Titel *</label><input class="fi" name="title" id="pf_title" required placeholder="T.ex. Takbyte Kungsbacka"></div>
      <div class="fg"><label>Undertitel</label><input class="fi" name="subtitle" id="pf_subtitle" placeholder="T.ex. Tegeltak · 165 m² · 2025"></div>
      <div class="frow">
        <div class="fg"><label>Kategori</label>
          <select class="fs" name="category" id="pf_category">
            <?php foreach ($categories as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Bildhöjd (px, för mosaik-layout)</label><input class="fi" type="number" name="height" id="pf_height" value="280"></div>
      </div>
      <div class="fg"><label>Ladda upp bild</label><input class="fi" type="file" name="image_file" id="pf_image_file" accept="image/jpeg,image/png,image/webp,image/gif"></div>
      <div class="fg"><label>...eller bild-URL <span id="pf_image_required_hint">*</span></label><input class="fi" name="image" id="pf_image" placeholder="https://..."></div>
      <div class="fg"><label>Koppla till CRM-projekt (för att visa kundrecension)</label>
        <select class="fs" name="linked_project_id" id="pf_linked_project_id">
          <option value="">— Ingen koppling —</option>
          <?php foreach ($crmProjects as $cp): ?><option value="<?= $cp['id'] ?>"><?= e($cp['title']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="frow">
        <div class="fg"><label>Sorteringsordning</label><input class="fi" type="number" name="sort_order" id="pf_sort_order" value="0"></div>
        <div class="fg" style="display:flex;align-items:flex-end"><label style="display:flex;align-items:center;gap:8px;font-weight:500"><input type="checkbox" name="visible" id="pf_visible" checked> Synlig på webbplatsen</label></div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('portfolioModal')">Avbryt</button>
        <button class="btn btn--primary">Spara</button>
      </div>
    </form>
  </div>
</div>

<script>
function editPortfolio(p) {
  document.getElementById('portfolioModalTitle').textContent = 'Redigera projekt';
  document.getElementById('pf_id').value = p.id;
  document.getElementById('pf_title').value = p.title;
  document.getElementById('pf_subtitle').value = p.subtitle || '';
  document.getElementById('pf_category').value = p.category;
  document.getElementById('pf_height').value = p.height;
  document.getElementById('pf_image').value = p.image;
  document.getElementById('pf_image_file').value = '';
  document.getElementById('pf_linked_project_id').value = p.linked_project_id || '';
  document.getElementById('pf_sort_order').value = p.sort_order;
  document.getElementById('pf_visible').checked = !!parseInt(p.visible);
  openModal('portfolioModal');
}
document.getElementById('newPortfolioBtn').addEventListener('click', function() {
  document.getElementById('portfolioModalTitle').textContent = 'Lägg till projekt';
  document.getElementById('pf_id').value = '';
  document.querySelector('#portfolioModal form').reset();
  document.getElementById('pf_linked_project_id').value = '';
  document.getElementById('pf_visible').checked = true;
});
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
