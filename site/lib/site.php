<?php
declare(strict_types=1);
require_once __DIR__ . '/content.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function page_header(string $title, string $description, string $path, string $active = ''): void
{
    $fullTitle = $title === 'Finanse w Polsce' ? 'Finanse w Polsce | Kredyt LoliSoft' : $title . ' | Kredyt LoliSoft';
    $canonical = SITE_URL . $path;
    $nav = [
        '/' => ['Start', 'start'],
        '/tematy.php' => ['Tematy', 'tematy'],
        '/narzedzia.php' => ['Narzędzia', 'narzedzia'],
        '/slownik.php' => ['Słownik', 'slownik'],
        '/zrodla.php' => ['Źródła', 'zrodla'],
    ];
    ?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($fullTitle) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="pl_PL">
  <meta property="og:site_name" content="Kredyt LoliSoft">
  <meta property="og:title" content="<?= e($fullTitle) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:image" content="<?= SITE_URL ?>/og-image.png">
  <meta name="theme-color" content="#142b45">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<a class="skip" href="#main">Przejdź do treści</a>
<header class="site-header">
  <div class="topline"><div class="container topline-inner"><span>Przewodnik po finansach w Polsce</span><span>Treści sprawdzone: <?= e(REVIEW_DATE) ?></span></div></div>
  <div class="container header-inner">
    <a class="logo" href="/" aria-label="Kredyt LoliSoft — strona główna"><span class="logo-mark">K<span>.</span></span><span>kredyt<span class="logo-muted">.lolisoft.eu</span></span></a>
    <nav class="main-nav" aria-label="Nawigacja główna">
      <?php foreach ($nav as $url => [$label, $key]): ?><a href="<?= e($url) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <a class="header-cta" href="/kalkulator-rrso.php">Kalkulator RRSO <span aria-hidden="true">↗</span></a>
  </div>
</header>
<main id="main">
<?php
}

function page_footer(): void
{
    ?>
</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div><a class="logo footer-logo" href="/">K<span>.</span> <span>kredyt.lolisoft.eu</span></a><p>Praktyczne wyjaśnienia finansów dla osób prywatnych i zespołów zawodowych w Polsce.</p></div>
    <div><h2>Odkrywaj</h2><a href="/tematy.php">Wszystkie tematy</a><a href="/narzedzia.php">Kalkulatory</a><a href="/slownik.php">Słownik pojęć</a></div>
    <div><h2>Serwis</h2><a href="/zrodla.php">Źródła i aktualność</a><a href="/o-serwisie.php">O serwisie</a><a href="https://github.com/jomardyan/RSSO" rel="noopener noreferrer">Kod źródłowy ↗</a></div>
  </div>
  <div class="container footer-bottom"><span>© <?= date('Y') ?> LoliSoft</span><span>Materiały edukacyjne · Stan źródeł: <?= e(REVIEW_DATE) ?></span></div>
</footer>
<script src="/portal.js" defer></script>
</body></html>
<?php
}

function article_card(string $slug, array $article): void
{
    $topic = topics()[$article['topic']];
    ?>
    <article class="article-card" data-search="<?= e($article['title'] . ' ' . $article['summary'] . ' ' . $topic[0]) ?>" data-topic="<?= e($article['topic']) ?>">
      <div class="card-meta"><span><?= e($topic[0]) ?></span><span><?= e($article['audience']) ?></span></div>
      <h3><a href="/artykul.php?slug=<?= rawurlencode($slug) ?>"><?= e($article['title']) ?></a></h3>
      <p><?= e($article['summary']) ?></p>
      <a class="text-link" href="/artykul.php?slug=<?= rawurlencode($slug) ?>">Czytaj przewodnik <span aria-hidden="true">→</span></a>
    </article>
    <?php
}

function source_links(array $ids): void
{
    $all = sources();
    echo '<ul class="source-list">';
    foreach ($ids as $id) {
        if (!isset($all[$id])) continue;
        [$label, $url] = $all[$id];
        echo '<li><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($label) . ' <span aria-hidden="true">↗</span></a></li>';
    }
    echo '</ul>';
}
