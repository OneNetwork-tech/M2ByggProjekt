<?php
/**
 * CRM — Blogg (public-site blog post authoring)
 * Any logged-in staff member can create/edit/publish posts.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

function blog_slugify(string $s): string {
    $s = strtolower(trim($s));
    $map = ['å'=>'a','ä'=>'a','ö'=>'o','é'=>'e','ü'=>'u'];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'inlagg';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: blog_slugify($title);
        $slug = blog_slugify($slug);
        $status = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';
        $words = str_word_count(strip_tags($_POST['body'] ?? ''));
        $readMinutes = max(1, (int)round($words / 200));

        if ($title === '') {
            flash('Titel krävs.', 'error');
        } else {
            $dupe = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
            $dupe->execute([$slug, $id]);
            if ($dupe->fetchColumn()) {
                $slug .= '-' . substr(md5((string)microtime(true)), 0, 4);
            }

            if ($id) {
                $existing = $pdo->prepare("SELECT status, published_at FROM blog_posts WHERE id = ?");
                $existing->execute([$id]);
                $prev = $existing->fetch();
                $publishedAt = $prev['published_at'];
                if ($status === 'published' && !$publishedAt) {
                    $publishedAt = date('Y-m-d H:i:s');
                }
                $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, excerpt=?, body=?, cover_image=?, category=?, status=?, read_minutes=?, published_at=?, updated_at=datetime('now','localtime') WHERE id=?")
                    ->execute([$title, $slug, trim($_POST['excerpt'] ?? ''), $_POST['body'] ?? '', trim($_POST['cover_image'] ?? ''), $_POST['category'] ?? 'tak', $status, $readMinutes, $publishedAt, $id]);
                audit('blog_post_update', 'blog_post', $id);
                flash('Inlägg uppdaterat.');
            } else {
                $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
                $pdo->prepare("INSERT INTO blog_posts (title, slug, excerpt, body, cover_image, category, status, author_id, read_minutes, published_at) VALUES (?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$title, $slug, trim($_POST['excerpt'] ?? ''), $_POST['body'] ?? '', trim($_POST['cover_image'] ?? ''), $_POST['category'] ?? 'tak', $status, $me['id'], $readMinutes, $publishedAt]);
                $id = $pdo->lastInsertId();
                audit('blog_post_create', 'blog_post', $id);
                flash('Inlägg skapat.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM blog_comments WHERE post_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
        audit('blog_post_delete', 'blog_post', $id);
        flash('Inlägg borttaget.');
    } elseif ($action === 'hide_comment') {
        $cid = (int)($_POST['comment_id'] ?? 0);
        $pdo->prepare("UPDATE blog_comments SET hidden = 1 - hidden WHERE id = ?")->execute([$cid]);
        flash('Kommentarstatus ändrad.');
    }
    header('Location: blogg.php' . (isset($_POST['view']) ? '?view=' . urlencode($_POST['view']) : '')); exit;
}

$view = $_GET['view'] ?? 'posts';

$posts = $pdo->query("
    SELECT bp.*, u.name AS author_name,
           (SELECT COUNT(*) FROM blog_comments bc WHERE bc.post_id = bp.id AND bc.hidden = 0) AS comment_count
    FROM blog_posts bp LEFT JOIN users u ON u.id = bp.author_id
    ORDER BY bp.created_at DESC
")->fetchAll();

$editId = (int)($_GET['edit'] ?? 0);
$editPost = null;
if ($editId) {
    $s = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $s->execute([$editId]);
    $editPost = $s->fetch();
}

$comments = $pdo->query("
    SELECT bc.*, bp.title AS post_title, bp.slug AS post_slug
    FROM blog_comments bc JOIN blog_posts bp ON bp.id = bc.post_id
    ORDER BY bc.created_at DESC LIMIT 100
")->fetchAll();

$categories = ['tak'=>'Tak','fasad'=>'Fasad','rot'=>'ROT-avdrag','pris'=>'Priser','plat'=>'Plåt','mark'=>'Mark','ovrigt'=>'Övrigt'];

$crm_title = 'Blogg';
$crm_page  = 'blogg';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Blogg</h1>
    <div class="topbar__sub"><?= count($posts) ?> inlägg · publika webbplatsen</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" id="newPostBtn" onclick="openModal('postModal')">+ Nytt inlägg</button>
  </div>
</div>

<?php flash(); ?>

<div class="tabs" style="margin-bottom:16px">
  <a href="?view=posts" class="tab <?= $view==='posts'?'active':'' ?>">Inlägg</a>
  <a href="?view=comments" class="tab <?= $view==='comments'?'active':'' ?>">Kommentarer</a>
</div>

<?php if ($view === 'posts'): ?>
<div class="card" style="overflow:hidden">
  <table class="data">
    <thead><tr><th>Titel</th><th>Kategori</th><th>Status</th><th>Författare</th><th>Kommentarer</th><th>Skapad</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
    <tr>
      <td>
        <a href="?edit=<?= $p['id'] ?>" onclick="event.preventDefault();editPost(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" style="font-weight:550"><?= e($p['title']) ?></a>
        <?php if ($p['status']==='published'): ?><div style="font-size:11px;color:var(--gray)"><a href="/blogg/<?= e($p['slug']) ?>" target="_blank">/blogg/<?= e($p['slug']) ?> ↗</a></div><?php endif; ?>
      </td>
      <td style="font-size:12.5px"><?= e($categories[$p['category']] ?? $p['category']) ?></td>
      <td><span class="badge-<?= $p['status']==='published' ? 'success' : 'warning' ?>" style="padding:3px 9px;border-radius:20px;font-size:11.5px"><?= $p['status']==='published' ? 'Publicerad' : 'Utkast' ?></span></td>
      <td style="font-size:12.5px;color:var(--gray)"><?= e($p['author_name'] ?? '—') ?></td>
      <td style="font-size:12.5px"><?= (int)$p['comment_count'] ?></td>
      <td style="font-size:12px;color:var(--gray)"><?= e(substr($p['created_at'],0,10)) ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Ta bort detta inlägg permanent?')" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">Ta bort</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$posts): ?><tr><td colspan="7" style="padding:24px;color:var(--gray);font-size:13px">Inga inlägg ännu.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<div class="card" style="overflow:hidden">
  <table class="data">
    <thead><tr><th>Kommentar</th><th>Inlägg</th><th>Författare</th><th>Datum</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($comments as $c): ?>
    <tr>
      <td style="font-size:13px;max-width:340px"><?= e(mb_strimwidth($c['body'], 0, 140, '…')) ?></td>
      <td style="font-size:12.5px"><a href="/blogg/<?= e($c['post_slug']) ?>" target="_blank"><?= e($c['post_title']) ?></a></td>
      <td style="font-size:12.5px"><?= e($c['author_name']) ?></td>
      <td style="font-size:12px;color:var(--gray)"><?= e(substr($c['created_at'],0,16)) ?></td>
      <td><span class="badge-<?= $c['hidden'] ? 'danger' : 'success' ?>" style="padding:3px 9px;border-radius:20px;font-size:11.5px"><?= $c['hidden'] ? 'Dold' : 'Synlig' ?></span></td>
      <td>
        <form method="post" style="display:inline">
          <?= csrf_field() ?><input type="hidden" name="action" value="hide_comment"><input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
          <button class="btn btn--ghost btn--sm"><?= $c['hidden'] ? 'Visa' : 'Dölj' ?></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$comments): ?><tr><td colspan="6" style="padding:24px;color:var(--gray);font-size:13px">Inga kommentarer ännu.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="modal-bg" id="postModal">
  <div class="modal" style="max-width:720px">
    <h3 id="postModalTitle">Nytt inlägg</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="f_id" value="">
      <div class="fg"><label>Titel *</label><input class="fi" name="title" id="f_title" required></div>
      <div class="frow">
        <div class="fg"><label>Slug (URL, lämna tomt för auto)</label><input class="fi" name="slug" id="f_slug" placeholder="t-ex-takbyte-kostnad"></div>
        <div class="fg"><label>Kategori</label>
          <select class="fs" name="category" id="f_category">
            <?php foreach ($categories as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fg"><label>Ingress (kort sammanfattning för listan)</label><textarea class="fi" name="excerpt" id="f_excerpt" rows="2"></textarea></div>
      <div class="fg"><label>Omslagsbild (URL)</label><input class="fi" name="cover_image" id="f_cover" placeholder="https://..."></div>
      <div class="fg">
        <label>Innehåll (HTML — använd &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;table&gt; etc., formateras automatiskt)</label>
        <textarea class="fi" name="body" id="f_body" rows="12" style="font-family:monospace;font-size:13px"></textarea>
      </div>
      <div class="fg"><label>Status</label>
        <select class="fs" name="status" id="f_status">
          <option value="draft">Utkast</option>
          <option value="published">Publicerad</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('postModal')">Avbryt</button>
        <button class="btn btn--primary">Spara</button>
      </div>
    </form>
  </div>
</div>

<script>
function editPost(p) {
  document.getElementById('postModalTitle').textContent = 'Redigera inlägg';
  document.getElementById('f_id').value = p.id;
  document.getElementById('f_title').value = p.title;
  document.getElementById('f_slug').value = p.slug;
  document.getElementById('f_category').value = p.category;
  document.getElementById('f_excerpt').value = p.excerpt || '';
  document.getElementById('f_cover').value = p.cover_image || '';
  document.getElementById('f_body').value = p.body || '';
  document.getElementById('f_status').value = p.status;
  openModal('postModal');
}
document.getElementById('newPostBtn').addEventListener('click', function() {
  document.getElementById('postModalTitle').textContent = 'Nytt inlägg';
  document.getElementById('f_id').value = '';
  document.querySelector('#postModal form').reset();
});
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
