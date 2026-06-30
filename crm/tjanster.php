<?php
/**
 * CRM — Tjänster (public-site services index, shown at /tjanster)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

function svc_slugify(string $s): string {
    $s = strtolower(trim($s));
    $map = ['å'=>'a','ä'=>'a','ö'=>'o','é'=>'e','ü'=>'u'];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'tjanst';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = svc_slugify(trim($_POST['slug'] ?? '') ?: $title);

        $uploadError = null;
        $uploadedImage = handle_public_image_upload('cover_image_file', 'services', $uploadError);
        $coverImage = $uploadedImage ?? trim($_POST['cover_image'] ?? '');

        if ($uploadError) {
            flash($uploadError, 'error');
        } elseif ($title === '' || trim($_POST['category'] ?? '') === '') {
            flash('Titel och kategori krävs.', 'error');
        } else {
            $dupe = $pdo->prepare("SELECT id FROM services WHERE slug = ? AND id != ?");
            $dupe->execute([$slug, $id]);
            if ($dupe->fetchColumn()) $slug .= '-' . substr(md5((string)microtime(true)), 0, 4);

            $fields = [
                trim($_POST['category']), $_POST['icon_key'] ?? 'tools', $title, $slug,
                trim($_POST['description'] ?? ''), trim($_POST['price_label'] ?? ''),
                $_POST['detail_body'] ?? '', $coverImage,
                (int)($_POST['sort_order'] ?? 0), isset($_POST['visible']) ? 1 : 0,
            ];

            if ($id) {
                $pdo->prepare("UPDATE services SET category=?, icon_key=?, title=?, slug=?, description=?, price_label=?, detail_body=?, cover_image=?, sort_order=?, visible=?, updated_at=" . now_expr() . " WHERE id=?")
                    ->execute([...$fields, $id]);
                audit('service_update', 'service', $id);
                flash('Tjänst uppdaterad.');
            } else {
                $pdo->prepare("INSERT INTO services (category, icon_key, title, slug, description, price_label, detail_body, cover_image, sort_order, visible) VALUES (?,?,?,?,?,?,?,?,?,?)")
                    ->execute($fields);
                audit('service_create', 'service', $pdo->lastInsertId());
                flash('Tjänst skapad.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM services WHERE id=?")->execute([$id]);
        audit('service_delete', 'service', $id);
        flash('Tjänst borttagen.');
    } elseif ($action === 'toggle_visible') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE services SET visible = 1 - visible WHERE id=?")->execute([$id]);
        flash('Synlighet ändrad.');
    }
    header('Location: tjanster.php'); exit;
}

$services = $pdo->query("SELECT * FROM services ORDER BY category, sort_order, title")->fetchAll();
$icons = service_icon_presets();

$crm_title = 'Tjänster';
$crm_page  = 'tjanster';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Tjänster</h1>
    <div class="topbar__sub"><?= count($services) ?> tjänster · visas på /tjanster på webbplatsen</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" id="newServiceBtn" onclick="openModal('serviceModal')">+ Ny tjänst</button>
  </div>
</div>

<?php flash(); ?>

<div class="card" style="overflow:hidden">
  <table class="data">
    <thead><tr><th>Tjänst</th><th>Kategori</th><th>Pris</th><th>Ordning</th><th>Synlig</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($services as $s): ?>
    <tr>
      <td>
        <a href="#" onclick="event.preventDefault();editService(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)" style="font-weight:550"><?= e($s['title']) ?></a>
        <div style="font-size:11px;color:var(--gray)">/tjanster/<?= e($s['slug']) ?></div>
      </td>
      <td style="font-size:12.5px"><?= e($s['category']) ?></td>
      <td style="font-size:12.5px"><?= e($s['price_label'] ?: '—') ?></td>
      <td style="font-size:12.5px;color:var(--gray)"><?= (int)$s['sort_order'] ?></td>
      <td>
        <form method="post" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="toggle_visible"><input type="hidden" name="id" value="<?= $s['id'] ?>">
          <button class="badge-<?= $s['visible'] ? 'success' : 'danger' ?>" style="padding:3px 9px;border-radius:20px;font-size:11.5px;border:none;cursor:pointer"><?= $s['visible'] ? 'Synlig' : 'Dold' ?></button>
        </form>
      </td>
      <td>
        <form method="post" onsubmit="return confirm('Ta bort denna tjänst permanent?')" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">Ta bort</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$services): ?><tr><td colspan="6" style="padding:24px;color:var(--gray);font-size:13px">Inga tjänster ännu.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal-bg" id="serviceModal">
  <div class="modal" style="max-width:680px">
    <h3 id="serviceModalTitle">Ny tjänst</h3>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="sv_id" value="">
      <div class="frow">
        <div class="fg"><label>Titel *</label><input class="fi" name="title" id="sv_title" required></div>
        <div class="fg"><label>Slug (URL, lämna tomt för auto)</label><input class="fi" name="slug" id="sv_slug" placeholder="t-ex-balkongrenovering"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Kategori * (gruppering på sidan)</label><input class="fi" name="category" id="sv_category" required placeholder="Takarbeten / Fasad & Balkong / Mark & Övrigt"></div>
        <div class="fg"><label>Ikon</label>
          <select class="fs" name="icon_key" id="sv_icon_key">
            <?php foreach (array_keys($icons) as $k): ?><option value="<?= e($k) ?>"><?= e(ucfirst($k)) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fg"><label>Kort beskrivning (visas på kortet i listan)</label><textarea class="fi" name="description" id="sv_description" rows="2"></textarea></div>
      <div class="frow">
        <div class="fg"><label>Prisetikett</label><input class="fi" name="price_label" id="sv_price_label" placeholder="från 900 kr/m² / Fast pris"></div>
        <div class="fg"><label>Sorteringsordning</label><input class="fi" type="number" name="sort_order" id="sv_sort_order" value="0"></div>
      </div>
      <div class="fg"><label>Ladda upp omslagsbild (valfritt)</label><input class="fi" type="file" name="cover_image_file" id="sv_cover_image_file" accept="image/jpeg,image/png,image/webp,image/gif"></div>
      <div class="fg"><label>...eller omslagsbild-URL</label><input class="fi" name="cover_image" id="sv_cover_image" placeholder="https://..."></div>
      <div class="fg">
        <label>Detaljerad text (HTML — visas på tjänstens egen sida om ingen specialdesignad sida redan finns för denna slug)</label>
        <textarea class="fi" name="detail_body" id="sv_detail_body" rows="8" style="font-family:monospace;font-size:13px"></textarea>
      </div>
      <div class="fg"><label style="display:flex;align-items:center;gap:8px;font-weight:500"><input type="checkbox" name="visible" id="sv_visible" checked> Synlig på webbplatsen</label></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('serviceModal')">Avbryt</button>
        <button class="btn btn--primary">Spara</button>
      </div>
    </form>
  </div>
</div>

<script>
function editService(s) {
  document.getElementById('serviceModalTitle').textContent = 'Redigera tjänst';
  document.getElementById('sv_id').value = s.id;
  document.getElementById('sv_title').value = s.title;
  document.getElementById('sv_slug').value = s.slug;
  document.getElementById('sv_category').value = s.category;
  document.getElementById('sv_icon_key').value = s.icon_key;
  document.getElementById('sv_description').value = s.description || '';
  document.getElementById('sv_price_label').value = s.price_label || '';
  document.getElementById('sv_sort_order').value = s.sort_order;
  document.getElementById('sv_cover_image').value = s.cover_image || '';
  document.getElementById('sv_cover_image_file').value = '';
  document.getElementById('sv_detail_body').value = s.detail_body || '';
  document.getElementById('sv_visible').checked = !!parseInt(s.visible);
  openModal('serviceModal');
}
document.getElementById('newServiceBtn').addEventListener('click', function() {
  document.getElementById('serviceModalTitle').textContent = 'Ny tjänst';
  document.getElementById('sv_id').value = '';
  document.querySelector('#serviceModal form').reset();
  document.getElementById('sv_visible').checked = true;
});
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
