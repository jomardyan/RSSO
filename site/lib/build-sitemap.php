<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/content.php';

$paths = ['/', '/tematy.php', '/narzedzia.php', '/kalkulator-rrso.php', '/slownik.php', '/zrodla.php', '/o-serwisie.php'];
foreach (array_keys(articles()) as $slug) {
    $paths[] = '/artykul.php?slug=' . rawurlencode($slug);
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($paths as $path) {
    $url = htmlspecialchars(SITE_URL . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $xml .= "  <url><loc>{$url}</loc><lastmod>" . REVIEW_DATE . "</lastmod></url>\n";
}
$xml .= "</urlset>\n";

file_put_contents(__DIR__ . '/../sitemap.xml', $xml);
echo count($paths) . " URLs written\n";
