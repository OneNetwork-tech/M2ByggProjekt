<?php
/**
 * Dynamic sitemap — static pages stay hardcoded below; service and blog post
 * URLs are pulled live from the DB so newly added CRM content is included
 * automatically. Served as XML at /sitemap.xml (see .htaccess / router.php).
 */
require_once __DIR__ . '/crm/includes/db.php';

header('Content-Type: application/xml; charset=UTF-8');

$staticPages = [
    ['/',                  'weekly',  '1.0'],
    ['/tjanster',          'weekly',  '0.9'],
    ['/offert',            'monthly', '0.9'],
    ['/kontakt',           'monthly', '0.8'],
    ['/om-oss',            'monthly', '0.7'],
    ['/projekt',           'weekly',  '0.7'],
    ['/prisguide',         'monthly', '0.8'],
    ['/faq',               'monthly', '0.7'],
    ['/blogg',             'weekly',  '0.7'],
    ['/bli-partner',       'monthly', '0.5'],
    ['/fastighet',         'monthly', '0.6'],
    ['/integritetspolicy', 'yearly',  '0.3'],
];

$localSeoPages = [
    ['/goteborg',    '0.9'], ['/kungsbacka', '0.8'], ['/molndal',  '0.8'],
    ['/kungalv',     '0.8'], ['/lerum',      '0.7'], ['/alingsas', '0.7'],
    ['/trollhattan', '0.7'], ['/askim',      '0.7'], ['/hisingen', '0.7'],
    ['/molnlycke',   '0.7'],
];

$serviceSlugs = db()->query("SELECT slug, updated_at FROM services WHERE visible = 1 ORDER BY slug")->fetchAll();
$blogSlugs    = db()->query("SELECT slug, updated_at FROM blog_posts WHERE status = 'published' ORDER BY slug")->fetchAll();

function sitemap_url(string $path, string $changefreq, string $priority, ?string $lastmod = null): string {
    $url = '<url><loc>https://www.m2team.se' . htmlspecialchars($path, ENT_QUOTES) . '</loc>';
    if ($lastmod) $url .= '<lastmod>' . htmlspecialchars(substr($lastmod, 0, 10), ENT_QUOTES) . '</lastmod>';
    $url .= '<changefreq>' . $changefreq . '</changefreq><priority>' . $priority . '</priority></url>';
    return $url;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

echo "  <!-- Core pages -->\n";
foreach ($staticPages as [$path, $freq, $pri]) echo '  ' . sitemap_url($path, $freq, $pri) . "\n";

echo "  <!-- Services (database-driven) -->\n";
foreach ($serviceSlugs as $s) echo '  ' . sitemap_url('/tjanster/' . $s['slug'], 'monthly', '0.8', $s['updated_at']) . "\n";

echo "  <!-- Blog articles (database-driven) -->\n";
foreach ($blogSlugs as $b) echo '  ' . sitemap_url('/blogg/' . $b['slug'], 'monthly', '0.7', $b['updated_at']) . "\n";

echo "  <!-- Local SEO pages -->\n";
foreach ($localSeoPages as [$path, $pri]) echo '  ' . sitemap_url($path, 'monthly', $pri) . "\n";

echo '</urlset>' . "\n";
