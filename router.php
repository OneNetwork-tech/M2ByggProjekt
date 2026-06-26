<?php
/**
 * M2 Platform — Local Development Router
 * Emulates .htaccess clean URLs for PHP's built-in server.
 *
 * Usage:  php -S localhost:8080 router.php
 * Never deployed to production (Apache uses .htaccess instead).
 */

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = __DIR__ . $uri;

// 0. Dynamic sitemap → sitemap.php
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    return true;
}

// 1. Serve existing files directly (css, js, images, direct .php)
if ($uri !== '/' && (is_file($path))) {
    // Run PHP files, serve static assets as-is
    if (str_ends_with($path, '.php')) {
        chdir(dirname($path));
        require $path;
        return true;
    }
    return false; // let built-in server serve static file
}

// 2. Directory with index.php (e.g. /tjanster, /blogg, /goteborg, /crm)
if (is_dir($path) && is_file($path . '/index.php')) {
    chdir($path);
    require $path . '/index.php';
    return true;
}

// 3. Clean URL → .php (e.g. /kontakt → kontakt.php, /tjanster/takbyte → tjanster/takbyte.php)
$phpFile = __DIR__ . rtrim($uri, '/') . '.php';
if (is_file($phpFile)) {
    chdir(dirname($phpFile));
    require $phpFile;
    return true;
}

// 3b. Blog posts (database-driven) → /blogg/{slug} → /blogg/visa.php?slug={slug}
if (preg_match('#^/blogg/([a-z0-9-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    chdir(__DIR__ . '/blogg');
    require __DIR__ . '/blogg/visa.php';
    return true;
}

// 3c. Services added via CRM (no hand-built static page) → /tjanster/{slug} → /tjanster/visa.php?slug={slug}
if (preg_match('#^/tjanster/([a-z0-9-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    chdir(__DIR__ . '/tjanster');
    require __DIR__ . '/tjanster/visa.php';
    return true;
}

// 4. Root
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return true;
}

// 5. 404
http_response_code(404);
require __DIR__ . '/404.php';
return true;
