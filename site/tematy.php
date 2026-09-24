<?php
require_once __DIR__ . '/lib/site.php';
$selected = isset($_GET['topic']) && is_string($_GET['topic']) ? $_GET['topic'] : '';
if ($selected !== '' && !isset(topics()[$selected])) $selected = '';
page_header('Tematy finansowe', 'Przewodniki finansowe o kredytach, oszczędzaniu, inwestowaniu, podatkach, rynku i finansach firmy.', '/tematy.php', 'tematy');
?>
<div class="page-hero"><div class="container"><span class="section-kicker">BIBLIOTEKA WIEDZY</span><h1>Tematy finansowe</h1><p>Wybierz temat albo wyszukaj zagadnienie. Każdy przewodnik kończy się praktyczną listą kontrolną i źródłami.</p></div></div>
<section class="section container"><div class="filter-bar"><label for="article-search">Szukaj przewodnika</label><input id="article-search" type="search" placeholder="Np. RRSO, PPK, obligacje..." autocomplete="off"><div class="filter-chips" role="group" aria-label="Filtruj według tematu"><button type="button" class="chip<?= $selected === '' ? ' active' : '' ?>" data-filter="">Wszystkie</button><?php foreach (topics() as $slug => [$name]): ?><button type="button" class="chip<?= $selected === $slug ? ' active' : '' ?>" data-filter="<?= e($slug) ?>"><?= e($name) ?></button><?php endforeach; ?></div></div><p class="results-note" id="results-count" aria-live="polite"></p><div class="article-grid" id="article-grid"><?php foreach (articles() as $slug => $article) article_card($slug, $article); ?></div><p id="no-results" class="empty-state" hidden>Nie znaleziono przewodnika. Spróbuj innego hasła lub wybierz „Wszystkie”.</p></section>
<?php page_footer(); ?>
