<?php
/**
 * Public blog — single post view. Routed via /blogg/{slug} (see .htaccess / router.php).
 */
require_once __DIR__ . '/../crm/includes/db.php';
require_once dirname(__DIR__) . '/portal/includes/auth.php';
portal_start();

// Lightweight CSRF for this form — not crm/includes/auth.php's csrf_field()/csrf_check(),
// since that file also calls session_name(SESSION_NAME) which would collide with the
// portal session already started above (session name can't change after session_start()).
function blog_csrf_field(): string {
    if (empty($_SESSION['blog_csrf'])) $_SESSION['blog_csrf'] = bin2hex(random_bytes(24));
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['blog_csrf'], ENT_QUOTES, 'UTF-8') . '">';
}
function blog_csrf_check(): bool {
    return !empty($_SESSION['blog_csrf']) && hash_equals($_SESSION['blog_csrf'], $_POST['csrf'] ?? '');
}

$slug = $_GET['slug'] ?? '';
$s = db()->prepare("SELECT bp.*, u.name AS author_name FROM blog_posts bp LEFT JOIN users u ON u.id = bp.author_id WHERE bp.slug = ? AND bp.status = 'published'");
$s->execute([$slug]);
$post = $s->fetch();

if (!$post) {
    http_response_code(404);
    require_once __DIR__ . '/../404.php';
    exit;
}

$pu = portal_user(); // soft check — does not redirect if not logged in

$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'])) {
    if (!blog_csrf_check()) {
        $commentError = 'Sessionen har gått ut, ladda om sidan och försök igen.';
    } elseif (!$pu) {
        $commentError = 'Du måste vara inloggad i kundportalen för att kommentera.';
    } else {
        $body = trim($_POST['comment_body']);
        if ($body === '') {
            $commentError = 'Skriv en kommentar innan du skickar.';
        } elseif (!rate_limit_check('blog_comment', (string)$pu['id'], 10, 15)) {
            $commentError = 'Du har skickat för många kommentarer. Försök igen senare.';
        } else {
            db()->prepare("INSERT INTO blog_comments (post_id, portal_user_id, author_name, body) VALUES (?,?,?,?)")
                ->execute([$post['id'], $pu['id'], $pu['name'], $body]);
            rate_limit_record('blog_comment', (string)$pu['id'], true);
            header('Location: /blogg/' . $post['slug'] . '#kommentarer');
            exit;
        }
    }
}

$comments = db()->prepare("SELECT * FROM blog_comments WHERE post_id = ? AND hidden = 0 ORDER BY created_at ASC");
$comments->execute([$post['id']]);
$comments = $comments->fetchAll();

$cat_labels = ['tak'=>'Tak','fasad'=>'Fasad','rot'=>'ROT-avdrag','pris'=>'Priser','plat'=>'Plåt','mark'=>'Mark','ovrigt'=>'Övrigt'];
function blog_date_label(?string $dt): string {
    if (!$dt) return '';
    $months = ['jan','feb','mar','apr','maj','jun','jul','aug','sep','okt','nov','dec'];
    $ts = strtotime($dt);
    return (int)date('j', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
}
function comment_initials(string $name): string {
    $parts = array_filter(explode(' ', $name));
    $initials = array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice($parts, 0, 2));
    return implode('', $initials) ?: '?';
}

$page_title       = $post['title'];
$page_description = $post['excerpt'];
$active_page      = 'blogg';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="breadcrumb"><div class="container"><div class="breadcrumb__inner">
  <a href="/">Hem</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <a href="/blogg">Blogg</a>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  <span><?= e($post['title']) ?></span>
</div></div></div>

<?php if ($post['cover_image']): ?>
<div style="position:relative;height:380px;overflow:hidden">
  <img src="<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>" style="width:100%;height:100%;object-fit:cover;filter:brightness(.55)">
  <div style="position:absolute;inset:0;background:linear-gradient(0,rgba(29,29,31,.95) 0%,rgba(29,29,31,.35) 60%,transparent 100%);display:flex;align-items:flex-end">
    <div class="container" style="padding-bottom:32px">
      <span style="background:rgba(181,113,42,.9);color:#fff;font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;display:inline-block;margin-bottom:12px"><?= e($cat_labels[$post['category']] ?? $post['category']) ?></span>
      <h1 style="color:#fff;max-width:700px;font-size:clamp(1.5rem,3.2vw,2.3rem);margin-bottom:10px"><?= e($post['title']) ?></h1>
      <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;font-size:13px;color:rgba(255,255,255,.65)">
        <span><?= e(blog_date_label($post['published_at'])) ?></span>
        <span><?= (int)$post['read_minutes'] ?> min läsning</span>
        <span><?= e($post['author_name'] ?? 'M2 Bygg Team AB') ?></span>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="container" style="padding-top:48px">
  <span style="background:rgba(181,113,42,.12);color:var(--copper);font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;display:inline-block;margin-bottom:14px"><?= e($cat_labels[$post['category']] ?? $post['category']) ?></span>
  <h1 style="max-width:780px;margin-bottom:10px"><?= e($post['title']) ?></h1>
  <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;font-size:13px;color:var(--steel-lt);margin-bottom:20px">
    <span><?= e(blog_date_label($post['published_at'])) ?></span>
    <span><?= (int)$post['read_minutes'] ?> min läsning</span>
    <span><?= e($post['author_name'] ?? 'M2 Bygg Team AB') ?></span>
  </div>
</div>
<?php endif; ?>

<div class="container" style="max-width:760px;padding:48px 24px 60px">
  <div class="blog-body"><?= $post['body'] ?></div>

  <div class="blog-comments" id="kommentarer">
    <h3 style="font-family:var(--font-display);font-size:1.3rem;color:var(--coal);margin-bottom:20px"><?= count($comments) ?> kommentar<?= count($comments) === 1 ? '' : 'er' ?></h3>

    <?php foreach ($comments as $c): ?>
    <div class="blog-comment">
      <div class="blog-comment__avatar"><?= e(comment_initials($c['author_name'])) ?></div>
      <div>
        <span class="blog-comment__name"><?= e($c['author_name']) ?></span>
        <span class="blog-comment__date"><?= e(blog_date_label($c['created_at'])) ?></span>
        <div class="blog-comment__body"><?= nl2br(e($c['body'])) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$comments): ?>
    <p style="font-size:14px;color:var(--steel-lt);padding:12px 0">Inga kommentarer ännu. Bli först att kommentera!</p>
    <?php endif; ?>

    <div style="margin-top:24px">
      <?php if ($pu): ?>
      <?php if ($commentError): ?><p style="color:#DC2626;font-size:13.5px;margin-bottom:10px"><?= e($commentError) ?></p><?php endif; ?>
      <form method="post" class="blog-comment-form" style="display:flex;flex-direction:column;gap:10px">
        <?= blog_csrf_field() ?>
        <textarea name="comment_body" placeholder="Skriv en kommentar som <?= e($pu['name']) ?>…" required></textarea>
        <button class="btn btn--copper" style="align-self:flex-end">Skicka kommentar</button>
      </form>
      <?php else: ?>
      <div class="blog-comment-login">
        Du måste vara inloggad i <a href="/portal/login.php?redir=<?= urlencode('/blogg/' . $post['slug'] . '#kommentarer') ?>">kundportalen</a> för att kommentera.
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
