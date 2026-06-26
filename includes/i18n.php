<?php
/**
 * Lightweight i18n — cookie-based language switch (sv default, en supported).
 * Usage: require_once this file, then call t('some.key') to get the translated string.
 * Falls back to the Swedish string (or the key itself) if a translation is missing.
 */

function current_lang(): string {
    static $lang = null;
    if ($lang !== null) return $lang;
    $requested = $_GET['lang'] ?? $_COOKIE['m2_lang'] ?? 'sv';
    $lang = in_array($requested, ['sv', 'en'], true) ? $requested : 'sv';
    if (isset($_GET['lang']) && $lang !== ($_COOKIE['m2_lang'] ?? 'sv') && !headers_sent()) {
        setcookie('m2_lang', $lang, time() + 86400 * 365, '/', '', isset($_SERVER['HTTPS']), true);
    }
    return $lang;
}

function i18n_strings(): array {
    static $cache = [];
    $lang = current_lang();
    if (isset($cache[$lang])) return $cache[$lang];
    $path = __DIR__ . "/../lang/{$lang}.php";
    $cache[$lang] = is_file($path) ? require $path : [];
    return $cache[$lang];
}

function t(string $key, ?string $fallback = null): string {
    $strings = i18n_strings();
    if (isset($strings[$key])) return $strings[$key];
    if (current_lang() !== 'sv') {
        $sv = require __DIR__ . '/../lang/sv.php';
        if (isset($sv[$key])) return $sv[$key];
    }
    return $fallback ?? $key;
}

/** Renders a small SV/EN switcher. Pass true for $light on white/light backgrounds. */
function lang_switcher_html(bool $light = false): string {
    $lang = current_lang();
    $qs = $_GET;
    unset($qs['lang']);
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $svUrl = $base . '?' . http_build_query(array_merge($qs, ['lang' => 'sv']));
    $enUrl = $base . '?' . http_build_query(array_merge($qs, ['lang' => 'en']));
    $svActive = $lang === 'sv' ? ' style="font-weight:700;text-decoration:underline"' : '';
    $enActive = $lang === 'en' ? ' style="font-weight:700;text-decoration:underline"' : '';
    $class = 'lang-switcher' . ($light ? ' lang-switcher--light' : '');
    return '<span class="' . $class . '"><a href="' . htmlspecialchars($svUrl) . '"' . $svActive . '>SV</a> / <a href="' . htmlspecialchars($enUrl) . '"' . $enActive . '>EN</a></span>';
}
