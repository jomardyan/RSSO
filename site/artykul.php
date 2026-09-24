<?php
require_once __DIR__ . '/lib/site.php';
$slug = isset($_GET['slug']) && is_string($_GET['slug']) ? $_GET['slug'] : '';
$article = articles()[$slug] ?? null;
if ($article === null) {
    http_response_code(404);
    page_header('Nie znaleziono artykułu', 'Szukany przewodnik nie istnieje.', '/tematy.php', 'tematy');
    echo '<div class="container not-found"><h1>Nie znaleziono przewodnika</h1><p>Sprawdź adres lub przejdź do listy tematów.</p><a class="button button-primary" href="/tematy.php">Zobacz tematy</a></div>';
    page_footer();
    exit;
}
$topic = topics()[$article['topic']];
page_header($article['title'], $article['summary'], '/artykul.php?slug=' . rawurlencode($slug), 'tematy');
?>
<div class="article-hero"><div class="container"><nav class="breadcrumbs" aria-label="Ścieżka"><a href="/">Start</a><span>/</span><a href="/tematy.php?topic=<?= e($article['topic']) ?>"><?= e($topic[0]) ?></a><span>/</span><span aria-current="page"><?= e($article['title']) ?></span></nav><div class="article-hero-inner"><div><span class="section-kicker"><?= e($topic[0]) ?> · <?= e($article['audience']) ?></span><h1><?= e($article['title']) ?></h1><p><?= e($article['summary']) ?></p><div class="review-line">Przegląd treści: <?= e(REVIEW_DATE) ?> <span>·</span> Materiał edukacyjny</div></div></div></div></div>
<div class="container article-layout"><article class="article-body"><div class="key-point"><span>NAJWAŻNIEJSZE</span><p><?= e($article['takeaway']) ?></p></div><?php foreach ($article['sections'] as [$heading, $text]): ?><section><h2><?= e($heading) ?></h2><p><?= e($text) ?></p></section><?php endforeach; ?><section class="checklist"><h2>Lista do sprawdzenia</h2><ul><?php foreach ($article['checks'] as $check): ?><li><?= e($check) ?></li><?php endforeach; ?></ul></section><section class="article-sources"><h2>Źródła i dalsza lektura</h2><p>Otwórz materiały źródłowe, gdy potrzebujesz aktualnych stawek, szczegółów prawnych lub warunków produktu.</p><?php source_links($article['sources']); ?></section><div class="article-note">Treść ma charakter edukacyjny i nie jest indywidualną poradą finansową, podatkową ani prawną. Przed decyzją sprawdź dokument produktu i aktualny stan przepisów.</div></article><aside class="article-aside"><div class="aside-card"><span class="section-kicker">W PRAKTYCE</span><h2>Przelicz własny scenariusz</h2><p>Podstaw orientacyjne dane do kalkulatora i zobacz, jak zmienia się wynik.</p><a class="button button-primary" href="<?= e($article['tool'] ?? '/narzedzia.php') ?>">Otwórz narzędzia →</a></div><div class="aside-card pale"><h2>Pozostałe tematy</h2><p>Wiedza jest najbardziej przydatna, gdy łączysz ze sobą kilka perspektyw.</p><a class="text-link" href="/tematy.php?topic=<?= e($article['topic']) ?>">Więcej: <?= e($topic[0]) ?> →</a></div></aside></div>
<?php page_footer(); ?>
