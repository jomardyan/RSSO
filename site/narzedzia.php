<?php
require_once __DIR__ . '/lib/site.php';

function number_input(string $key, float $default, float $min, float $max): float
{
    if (!isset($_GET[$key]) || !is_string($_GET[$key])) return $default;
    $value = filter_var(str_replace(',', '.', trim($_GET[$key])), FILTER_VALIDATE_FLOAT);
    return $value !== false && is_finite((float)$value) && $value >= $min && $value <= $max ? (float)$value : $default;
}
function fmt(float $n, int $decimals = 2): string { return number_format($n, $decimals, ',', ' '); }
function field(string $id, string $label, float $value, string $suffix, float $min, float $max, string $step = '0.01'): void
{
    echo '<label class="form-field" for="' . e($id) . '"><span>' . e($label) . '</span><span class="input-wrap"><input id="' . e($id) . '" name="' . e($id) . '" type="number" inputmode="decimal" min="' . e((string)$min) . '" max="' . e((string)$max) . '" step="' . e($step) . '" value="' . e((string)$value) . '" required><span>' . e($suffix) . '</span></span></label>';
}

$tool = isset($_GET['tool']) && is_string($_GET['tool']) ? $_GET['tool'] : '';
if (!in_array($tool, ['rata', 'poduszka', 'oszczednosci'], true)) $tool = '';
$principal = number_input('kwota', 350000, 1000, 100000000);
$rate = number_input('oprocentowanie', 7, 0, 100);
$years = number_input('lata', 25, 1, 50);
$monthlyRate = $rate / 1200;
$months = (int)round($years * 12);
$payment = $monthlyRate === 0.0 ? $principal / $months : $principal * $monthlyRate / (1 - pow(1 + $monthlyRate, -$months));
$essential = number_input('wydatki', 5000, 1, 1000000);
$reserveMonths = number_input('miesiace', 6, 1, 36);
$saved = number_input('odlozone', 10000, 0, 100000000);
$target = $essential * $reserveMonths;
$initial = number_input('start', 10000, 0, 100000000);
$contribution = number_input('wplata', 500, 0, 1000000);
$savingYears = number_input('okres', 10, 1, 50);
$savingRate = number_input('stopa', 4, 0, 100);
$savingMonths = (int)round($savingYears * 12);
$r = $savingRate / 1200;
$factor = pow(1 + $r, $savingMonths);
$future = $initial * $factor + ($r === 0.0 ? $contribution * $savingMonths : $contribution * (($factor - 1) / $r));
$paidIn = $initial + $contribution * $savingMonths;
page_header('Kalkulatory finansowe', 'Oblicz orientacyjną ratę kredytu, cel poduszki finansowej i przyszłą wartość oszczędności.', '/narzedzia.php', 'narzedzia');
?>
<div class="page-hero"><div class="container"><span class="section-kicker">NARZĘDZIA</span><h1>Przelicz własny scenariusz</h1><p>Proste kalkulatory pomagają zrozumieć zależności. Wyniki są orientacyjne i zależą od przyjętych założeń.</p></div></div>
<section class="section container tools-index"><a href="#rata"><span>01</span><strong>Rata kredytu</strong><small>Kwota, okres i oprocentowanie ↗</small></a><a href="#poduszka"><span>02</span><strong>Poduszka finansowa</strong><small>Cel i brakująca kwota ↗</small></a><a href="#oszczednosci"><span>03</span><strong>Wzrost oszczędności</strong><small>Wpłaty i procent składany ↗</small></a><a href="/kalkulator-rrso.php"><span>04</span><strong>Kalkulator RRSO</strong><small>Harmonogram przepływów ↗</small></a></section>
<div class="container tools-stack">
<section id="rata" class="tool-section"><div class="tool-intro"><span class="section-kicker">01 / KREDYT</span><h2>Rata równa kredytu</h2><p>Uproszczona symulacja raty kapitałowo-odsetkowej przy stałej stopie przez cały okres. Nie obejmuje prowizji, ubezpieczeń ani zmiany oprocentowania.</p></div><div class="tool-box"><form method="get" action="/narzedzia.php#rata"><input type="hidden" name="tool" value="rata"><?php field('kwota', 'Kwota kredytu', $principal, 'zł', 1000, 100000000, '1000'); field('oprocentowanie', 'Oprocentowanie roczne', $rate, '%', 0, 100); field('lata', 'Okres spłaty', $years, 'lat', 1, 50, '1'); ?><button class="button button-primary" type="submit">Oblicz ratę →</button></form><div class="tool-result" aria-live="polite"><span>SZACOWANA RATA MIESIĘCZNA</span><strong><?= fmt($payment) ?> zł</strong><dl><div><dt>Suma rat</dt><dd><?= fmt($payment * $months) ?> zł</dd></div><div><dt>Odsetki</dt><dd><?= fmt($payment * $months - $principal) ?> zł</dd></div></dl><small>Założenie: <?= (int)$months ?> równych rat, stałe oprocentowanie <?= fmt($rate) ?>% rocznie.</small></div></div></section>
<section id="poduszka" class="tool-section"><div class="tool-intro"><span class="section-kicker">02 / BUDŻET</span><h2>Cel poduszki finansowej</h2><p>Wybierz liczbę miesięcy, przez które chcesz pokrywać niezbędne wydatki z rezerwy. Nie jest to ustawowy ani uniwersalny poziom.</p></div><div class="tool-box"><form method="get" action="/narzedzia.php#poduszka"><input type="hidden" name="tool" value="poduszka"><?php field('wydatki', 'Niezbędne wydatki miesięczne', $essential, 'zł', 1, 1000000, '100'); field('miesiace', 'Liczba miesięcy', $reserveMonths, 'mies.', 1, 36, '1'); field('odlozone', 'Już odłożone', $saved, 'zł', 0, 100000000, '100'); ?><button class="button button-primary" type="submit">Oblicz cel →</button></form><div class="tool-result" aria-live="polite"><span>CEL REZERWY</span><strong><?= fmt($target, 0) ?> zł</strong><dl><div><dt>Już odłożone</dt><dd><?= fmt($saved, 0) ?> zł</dd></div><div><dt>Do celu brakuje</dt><dd><?= fmt(max(0, $target - $saved), 0) ?> zł</dd></div></dl><small>Przyjęto <?= fmt($reserveMonths, 0) ?> miesięcy wydatków koniecznych.</small></div></div></section>
<section id="oszczednosci" class="tool-section"><div class="tool-intro"><span class="section-kicker">03 / OSZCZĘDZANIE</span><h2>Wzrost oszczędności</h2><p>Model procentu składanego z wpłatą na koniec każdego miesiąca. Założona stała stopa jest scenariuszem, a nie gwarancją zysku.</p></div><div class="tool-box"><form method="get" action="/narzedzia.php#oszczednosci"><input type="hidden" name="tool" value="oszczednosci"><?php field('start', 'Kwota początkowa', $initial, 'zł', 0, 100000000, '100'); field('wplata', 'Wpłata miesięczna', $contribution, 'zł', 0, 1000000, '50'); field('okres', 'Czas oszczędzania', $savingYears, 'lat', 1, 50, '1'); field('stopa', 'Założona stopa roczna', $savingRate, '%', 0, 100); ?><button class="button button-primary" type="submit">Oblicz wartość →</button></form><div class="tool-result" aria-live="polite"><span>SZACOWANA WARTOŚĆ KOŃCOWA</span><strong><?= fmt($future, 0) ?> zł</strong><dl><div><dt>Wpłacony kapitał</dt><dd><?= fmt($paidIn, 0) ?> zł</dd></div><div><dt>Przyrost nominalny</dt><dd><?= fmt($future - $paidIn, 0) ?> zł</dd></div></dl><small>Przed podatkiem, opłatami i inflacją. Stopa jest stała w symulacji.</small></div></div></section>
</div>
<section class="section container"><div class="callout"><h2>Potrzebujesz RRSO?</h2><p>Zaawansowany kalkulator oblicza stopę dla pojedynczej spłaty lub datowanego harmonogramu i pozwala sprawdzić limity kosztów.</p><a class="button button-primary" href="/kalkulator-rrso.php">Otwórz kalkulator RRSO ↗</a></div></section>
<?php page_footer(); ?>
