<?php
require_once __DIR__ . '/lib/site.php';
$terms = [
    ['BFG', 'Bankowy Fundusz Gwarancyjny. Chroni kwalifikowane depozyty do ustawowego limitu i na określonych zasadach.', 'lokaty-i-bfg'],
    ['DSCR', 'Wskaźnik pokrycia obsługi długu: relacja gotówki dostępnej na spłatę do wymaganych rat i odsetek w danym okresie.', 'analiza-zadluzenia'],
    ['ETF', 'Fundusz lub produkt giełdowy śledzący indeks bądź strategię. Cena i wartość mogą się zmieniać.', 'fundusze-etf-akcje'],
    ['Inflacja CPI', 'Zmiana poziomu cen koszyka towarów i usług konsumpcyjnych mierzona przez GUS.', 'inflacja-i-realny-zysk'],
    ['Kapitalizacja', 'Dopisanie odsetek do kapitału, dzięki czemu w kolejnym okresie mogą także pracować.', 'poduszka-finansowa'],
    ['Marża kredytowa', 'Część oprocentowania ustalona w umowie przez kredytodawcę; przy zmiennej stopie dodawana do wskaźnika.', 'kredyt-hipoteczny'],
    ['MPKK', 'Maksymalne pozaodsetkowe koszty kredytu konsumenckiego, określane według zasad ustawowych.', 'rrso-i-koszt-kredytu'],
    ['PIT-38', 'Zeznanie podatkowe używane m.in. przy rozliczeniu dochodu ze sprzedaży akcji.', 'podatek-od-inwestycji'],
    ['PPK', 'Pracownicze Plany Kapitałowe — program długoterminowego oszczędzania z wpłatami uczestnika i pracodawcy.', 'ppk'],
    ['RRSO', 'Rzeczywista roczna stopa oprocentowania — miara kosztu kredytu w skali roku, obejmująca określone koszty.', 'rrso-i-koszt-kredytu'],
    ['Spread walutowy', 'Różnica między kursem kupna i sprzedaży waluty; składnik kosztu wymiany.', 'kursy-walut'],
    ['Stopa referencyjna NBP', 'Podstawowa stopa polityki pieniężnej NBP, ustalana przez Radę Polityki Pieniężnej.', 'stopy-procentowe'],
    ['Zdolność kredytowa', 'Ocena możliwości spłaty zobowiązania w uzgodnionym terminie, wykonywana przez kredytodawcę.', 'kredyt-hipoteczny'],
];
page_header('Słownik finansowy', 'Zrozum pojęcia: RRSO, BFG, PPK, ETF, inflacja, DSCR i inne terminy finansowe.', '/slownik.php', 'slownik');
?>
<div class="page-hero"><div class="container"><span class="section-kicker">SŁOWNIK</span><h1>Finanse bez żargonu</h1><p>Krótkie definicje popularnych terminów z odnośnikami do przewodników.</p></div></div><section class="section container"><label class="search-label" for="term-search">Szukaj pojęcia</label><input class="standalone-search" id="term-search" type="search" placeholder="Wpisz termin lub fragment definicji" autocomplete="off"><div class="term-list" id="term-list"><?php foreach ($terms as [$term, $definition, $slug]): ?><article class="term" data-search="<?= e($term . ' ' . $definition) ?>"><h2><?= e($term) ?></h2><p><?= e($definition) ?></p><a class="text-link" href="/artykul.php?slug=<?= rawurlencode($slug) ?>">Więcej w przewodniku →</a></article><?php endforeach; ?></div><p id="term-empty" class="empty-state" hidden>Nie znaleziono pojęcia.</p></section>
<?php page_footer(); ?>
