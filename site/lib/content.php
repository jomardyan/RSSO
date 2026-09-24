<?php
declare(strict_types=1);

const SITE_URL = 'https://kredyt.lolisoft.eu';
const REVIEW_DATE = '2026-09-24';

function sources(): array
{
    return [
        'uokik-credit' => ['UOKiK · Twoje finanse', 'https://www.uokik.gov.pl/public/twoje-finanse'],
        'law-credit' => ['Dziennik Ustaw · ustawa o kredycie konsumenckim, art. 36a', 'https://eli.gov.pl/api/acts/DU/2024/1497/text.html'],
        'uokik-home' => ['UOKiK · ustawa o kredycie hipotecznym', 'https://finanse.uokik.gov.pl/chf/ustawa-o-kredycie-hipotecznym/'],
        'uokik-early' => ['UOKiK · wcześniejsza spłata', 'https://finanse.uokik.gov.pl/faq/'],
        'bfg' => ['BFG · wysokość gwarancji', 'https://bfg.pl/gwarantowanie-depozytow/zasady-gwarantowania-depozytow/wysokosc-gwarancji-bfg/'],
        'bfg-faq' => ['BFG · pytania i odpowiedzi', 'https://bfg.pl/gwarantowanie-depozytow/najczestsze-pytania-i-odpowiedzi/'],
        'bonds' => ['Obligacje Skarbowe · oferta', 'https://www.obligacjeskarbowe.pl/oferta/'],
        'bonds-guide' => ['Obligacje Skarbowe · rodzaje', 'https://www.obligacjeskarbowe.pl/o-obligacjach/oszczedzanie-z-detalicznymi-obligacjami-skarbowymi-jest-proste/'],
        'knf' => ['KNF · inwestuj świadomie', 'https://www.knf.gov.pl/dla_konsumenta/kampanie_informacyjne/inwestuj_swiadomie?articleId=70530&p_id=18'],
        'knf-s' => ['KNF · Rekomendacja S', 'https://www.knf.gov.pl/knf/pl/komponenty/img/Rekomendacja_S_nowelizacja_czerwiec_2023_82872.pdf'],
        'tax' => ['Podatki.gov.pl · stawki i limity', 'https://www.podatki.gov.pl/podatki-osobiste/pit/stawki-i-limity/'],
        'tax-shares' => ['Podatki.gov.pl · zbycie akcji', 'https://www.podatki.gov.pl/podatki-osobiste/pit/informacje-podstawowe/co-jest-opodatkowane/zbycie-akcji'],
        'ppk' => ['Moje PPK · wpłaty', 'https://www.mojeppk.pl/faq/pracownik/wplaty-do-ppk_ile-wynosi-wplata-pracodawcy-a-ile-pracownika.html'],
        'gus' => ['GUS · wskaźniki cen konsumpcyjnych', 'https://stat.gov.pl/obszary-tematyczne/ceny-handel/wskazniki-cen/wskazniki-cen-towarow-i-uslug-konsumpcyjnych-pot-inflacja-/'],
        'nbp' => ['NBP · kursy walut', 'https://nbp.pl/statystyka-i-sprawozdawczosc/kursy/'],
        'nbp-rates' => ['NBP · komunikat RPP, marzec 2026', 'https://nbp.pl/wp-content/uploads/2026/03/Komunikat-RPP-2026.03-marzec.pdf'],
        'parp' => ['PARP · faktoring', 'https://www.parp.gov.pl/component/content/article/77467:do-czego-sluzy-i-jak-dziala-faktoring'],
    ];
}

function topics(): array
{
    return [
        'kredyty' => ['Kredyty i hipoteki', 'Porównuj całkowity koszt, ratę, ryzyko stopy i warunki umowy.', '⌂'],
        'oszczedzanie' => ['Oszczędzanie', 'Buduj rezerwę i sprawdzaj ochronę depozytów oraz realny zysk.', '◉'],
        'inwestowanie' => ['Inwestowanie', 'Rozumiej instrument, koszt, płynność i ryzyko straty.', '↗'],
        'podatki' => ['Podatki i emerytura', 'Orientuj się w podatkach od kapitału i programie PPK.', '▤'],
        'rynek' => ['Rynek i gospodarka', 'Czytaj stopy, inflację i kursy w kontekście decyzji.', '◫'],
        'firma' => ['Finanse firmy', 'Kontroluj przepływy, finansowanie i koszt kapitału.', '▣'],
    ];
}

function articles(): array
{
    return [
        'rrso-i-koszt-kredytu' => [
            'title' => 'RRSO i całkowity koszt kredytu', 'topic' => 'kredyty', 'audience' => 'Konsument',
            'summary' => 'Jak porównać dwie oferty, które mają różne prowizje, raty i czas spłaty.',
            'takeaway' => 'Porównuj RRSO razem z całkowitą kwotą do zapłaty przy tej samej kwocie i okresie kredytu.',
            'sections' => [
                ['Co pokazuje RRSO', 'Rzeczywista roczna stopa oprocentowania wyraża koszt kredytu w skali roku. Obejmuje odsetki oraz koszty, które zgodnie z zasadami obliczania RRSO należy uwzględnić, na przykład prowizje i obowiązkowe usługi dodatkowe. Sama nominalna stopa procentowa nie opisuje pełnej ceny pożyczki.'],
                ['Jak porównywać oferty', 'Zestawiaj oferty dla tej samej kwoty, okresu i sposobu spłaty. Sprawdź kwotę wypłaconą, sumę rat, prowizję, ubezpieczenie, warunki promocji i koszt wcześniejszej spłaty. Przy krótkich pożyczkach roczne przeliczenie może dawać bardzo wysoką wartość RRSO, więc sprawdź też koszt w złotych.'],
                ['Limit kosztów pozaodsetkowych', 'W kredycie konsumenckim na co najmniej 30 dni maksymalne koszty pozaodsetkowe według art. 36a to 10% całkowitej kwoty kredytu plus 10% tej kwoty za każdy rok spłaty, z limitem 45%. Dla okresu krótszego niż 30 dni wzór wynosi 5% całkowitej kwoty kredytu. Limit nie oznacza, że opłaty na jego poziomie są zawsze uzasadnione.'],
                ['Kiedy użyć kalkulatora', 'Kalkulator pomaga policzyć stopę dla jednej spłaty albo harmonogramu przepływów. Wynik zależy od tego, czy wpisano wszystkie należne koszty i poprawne daty. Wynik orientacyjny nie zastępuje formularza informacyjnego ani oceny konkretnej umowy.'],
            ], 'checks' => ['Poproś o formularz informacyjny.', 'Porównaj całkowitą kwotę do zapłaty i RRSO.', 'Sprawdź opłaty za usługi dodatkowe i wcześniejszą spłatę.'],
            'sources' => ['law-credit', 'uokik-credit', 'uokik-early'], 'tool' => '/kalkulator-rrso.php',
        ],
        'kredyt-hipoteczny' => [
            'title' => 'Kredyt hipoteczny: decyzje przed podpisaniem', 'topic' => 'kredyty', 'audience' => 'Konsument',
            'summary' => 'Rata to tylko część obrazu. Sprawdź wkład własny, oprocentowanie, koszty i odporność budżetu.',
            'takeaway' => 'Przetestuj budżet przy wyższej racie i porównaj całkowity koszt w jednakowych scenariuszach.',
            'sections' => [
                ['Zdolność i bufor', 'Bank ocenia dochody, koszty utrzymania, obecne zobowiązania i okres finansowania. Własny budżet warto policzyć bardziej zachowawczo: po opłaceniu raty powinny pozostać środki na codzienne wydatki i nieprzewidziane zdarzenia.'],
                ['Stała czy zmienna stopa', 'Przy stopie zmiennej rata może reagować na zmianę wskaźnika i marży określonych w umowie. Okresowo stałe oprocentowanie stabilizuje ratę na ustalony czas, po którym warunki mogą się zmienić. Porównaj symulacje dla kilku poziomów oprocentowania i przeczytaj zasady jego zmiany.'],
                ['Koszty i prawa', 'Obok raty sprawdź prowizję, ubezpieczenia, koszty zabezpieczeń, wymagane konto i opłaty dodatkowe. Konsument może wcześniej spłacić kredyt hipoteczny w całości lub części; rozliczenie kosztów i ewentualna rekompensata zależą od przepisów i umowy.'],
            ], 'checks' => ['Zestaw oferty na tej samej kwocie i okresie.', 'Policz ratę także przy wyższym oprocentowaniu.', 'Przeczytaj warunki wcześniejszej spłaty i produktów dodatkowych.'],
            'sources' => ['uokik-home', 'knf-s'], 'tool' => '/narzedzia.php?tool=rata',
        ],
        'wczesniejsza-splata' => [
            'title' => 'Wcześniejsza spłata kredytu', 'topic' => 'kredyty', 'audience' => 'Konsument',
            'summary' => 'Jak myśleć o oszczędności na odsetkach i rozliczeniu części kosztów.',
            'takeaway' => 'Poproś kredytodawcę o szczegółowe rozliczenie kosztów przypadających na skrócony okres.',
            'sections' => [
                ['Co się zmienia', 'Po wcześniejszej spłacie odsetki za okres, w którym kredyt nie będzie już wykorzystywany, nie powinny być naliczane. W kredycie konsumenckim całkowity koszt ulega obniżeniu o koszty dotyczące skróconego okresu, nawet jeśli część kosztów zapłacono z góry.'],
                ['Co sprawdzić', 'Ustal saldo kapitału, planowaną datę spłaty, ewentualną prowizję lub rekompensatę oraz sposób rozliczenia wcześniej pobranych kosztów. Zachowaj potwierdzenie przelewu i pisemne rozliczenie. Dla hipoteki stosuje się odrębne przepisy i warunki umowy.'],
            ], 'checks' => ['Poproś o kwotę do spłaty na konkretny dzień.', 'Sprawdź umowę pod kątem rekompensaty.', 'Zweryfikuj zwrot lub obniżenie kosztów.'],
            'sources' => ['uokik-early', 'uokik-home'], 'tool' => '/narzedzia.php?tool=rata',
        ],
        'poduszka-finansowa' => [
            'title' => 'Poduszka finansowa i plan oszczędzania', 'topic' => 'oszczedzanie', 'audience' => 'Konsument',
            'summary' => 'Wyznacz cel rezerwy, a potem dobierz dostępne i zrozumiałe miejsce na środki.',
            'takeaway' => 'Wielkość rezerwy dobierz do wydatków i stabilności dochodu; trzy do sześciu miesięcy to praktyczny punkt startowy, nie reguła ustawowa.',
            'sections' => [
                ['Policz podstawę', 'Zsumuj niezbędne miesięczne koszty: mieszkanie, żywność, transport, zdrowie i minimalne raty. Pomnóż je przez liczbę miesięcy, przez które chcesz móc finansować te wydatki bez bieżącego dochodu. Osoby o nieregularnych dochodach mogą wybrać większy bufor.'],
                ['Dostępność ma znaczenie', 'Pieniądze awaryjne powinny być dostępne, gdy są potrzebne. Zwróć uwagę na warunki wypłaty z lokaty, oprocentowanie po okresie promocyjnym, opłaty i ochronę depozytów. Inflacja obniża siłę nabywczą nominalnej kwoty.'],
            ], 'checks' => ['Policz miesięczne koszty konieczne.', 'Ustaw automatyczny przelew po wypłacie.', 'Raz do roku sprawdź wielkość rezerwy.'],
            'sources' => ['bfg', 'gus'], 'tool' => '/narzedzia.php?tool=poduszka',
        ],
        'lokaty-i-bfg' => [
            'title' => 'Lokaty, konta i gwarancje BFG', 'topic' => 'oszczedzanie', 'audience' => 'Konsument',
            'summary' => 'Co oznacza limit 100 000 euro i jak czytać reklamowane oprocentowanie.',
            'takeaway' => 'Ochrona BFG jest liczona co do zasady na deponenta w danym banku lub kasie, a nie na pojedynczy rachunek.',
            'sections' => [
                ['Zakres gwarancji', 'BFG gwarantuje kwalifikowane środki do równowartości 100 000 euro na deponenta w danym banku lub kasie. W określonych sytuacjach ustawowych dostępna jest czasowo wyższa ochrona; szczegóły i wyłączenia sprawdź bezpośrednio w BFG. Konta w tym samym banku co do zasady sumują się do limitu.'],
                ['Porównanie ofert', 'Sprawdź okres promocji, maksymalną kwotę objętą oprocentowaniem, warunek nowych środków, obowiązek aktywności oraz oprocentowanie po promocji. Porównuj zysk po podatku i po uwzględnieniu utraty odsetek przy wcześniejszym zerwaniu lokaty.'],
            ], 'checks' => ['Sprawdź bank lub kasę na stronie BFG.', 'Zsumuj środki w tej samej instytucji.', 'Przeczytaj warunki promocji i wypłaty.'],
            'sources' => ['bfg', 'bfg-faq', 'tax'], 'tool' => '/narzedzia.php?tool=oszczednosci',
        ],
        'obligacje-skarbowe' => [
            'title' => 'Detaliczne obligacje skarbowe', 'topic' => 'inwestowanie', 'audience' => 'Konsument',
            'summary' => 'Stałe, zmienne i indeksowane inflacją obligacje różnią się sposobem naliczania odsetek.',
            'takeaway' => 'Przed zakupem przeczytaj list emisyjny konkretnej serii: oprocentowanie, kapitalizację i opłatę za przedterminowy wykup.',
            'sections' => [
                ['Rodzaje oprocentowania', 'W detalicznej ofercie Skarbu Państwa są obligacje o oprocentowaniu stałym, zmiennym oraz powiązanym z inflacją w kolejnych okresach odsetkowych. Parametry zmieniają się między emisjami, więc stawka z reklamy innego miesiąca nie opisuje Twojej serii.'],
                ['Horyzont i płynność', 'Dobierz termin do planowanego wykorzystania pieniędzy. Sprawdź, kiedy wypłacane są odsetki, czy są kapitalizowane oraz warunki przedterminowego wykupu. Obligacje detaliczne Skarbu Państwa nie są depozytem objętym gwarancją BFG.'],
            ], 'checks' => ['Porównaj aktualne serie na oficjalnej stronie.', 'Przeczytaj list emisyjny.', 'Policz zysk po podatku i opłacie przy wcześniejszym wykupie.'],
            'sources' => ['bonds', 'bonds-guide', 'bfg'], 'tool' => '/narzedzia.php?tool=oszczednosci',
        ],
        'fundusze-etf-akcje' => [
            'title' => 'Fundusze, ETF i akcje: pierwsze porównanie', 'topic' => 'inwestowanie', 'audience' => 'Konsument',
            'summary' => 'Dywersyfikacja, opłaty, płynność i ryzyko kursowe przed pierwszą inwestycją.',
            'takeaway' => 'Najpierw ustal horyzont i akceptowaną stratę; potem porównaj ekspozycję, koszty oraz dokumenty produktu.',
            'sections' => [
                ['Co kupujesz', 'Akcja to udział w spółce. Fundusz zbiera kapitał wielu inwestorów zgodnie z polityką inwestycyjną. ETF jest instrumentem giełdowym śledzącym określony indeks lub strategię. Szeroki koszyk może ograniczać ryzyko pojedynczej spółki, ale nie usuwa ryzyka całego rynku.'],
                ['Koszt i ryzyko', 'Sprawdź opłatę za zarządzanie, koszty transakcyjne, spread, walutę notowania i ekspozycję walutową. Wartość inwestycji może spadać; także fundusz obligacji może mieć okresowe straty. Zweryfikuj uprawnienia pośrednika w rejestrze KNF.'],
            ], 'checks' => ['Przeczytaj dokument kluczowych informacji.', 'Porównaj łączne koszty.', 'Sprawdź pośrednika w rejestrze KNF.'],
            'sources' => ['knf'], 'tool' => '/narzedzia.php?tool=oszczednosci',
        ],
        'podatek-od-inwestycji' => [
            'title' => 'Podatek od lokat, akcji i funduszy', 'topic' => 'podatki', 'audience' => 'Konsument',
            'summary' => 'Kiedy działa podatek 19% i dlaczego różne produkty rozlicza się odmiennie.',
            'takeaway' => 'Zysk z lokaty jest zwykle rozliczany przez płatnika, a sprzedaż akcji wymaga zwykle PIT-38.',
            'sections' => [
                ['Stawka i podstawa', 'Polskie przepisy przewidują 19% podatek od wielu dochodów kapitałowych, między innymi od odsetek od lokat i dochodu ze sprzedaży akcji. Podatek od zbycia akcji liczy się od dochodu, czyli przychodu pomniejszonego o kwalifikowane koszty. Strata ma własne zasady rozliczenia.'],
                ['Praktyka rozliczenia', 'Bank co do zasady pobiera zryczałtowany podatek od odsetek. Dochody ze sprzedaży akcji są wykazywane w PIT-38. Przy inwestycjach zagranicznych, kilku rachunkach lub szczególnych zwolnieniach sposób rozliczenia może być bardziej złożony. Zachowaj dokumenty i sprawdź aktualne instrukcje podatkowe.'],
            ], 'checks' => ['Ustal rodzaj przychodu.', 'Zachowaj PIT-8C i historię transakcji, jeśli dotyczy.', 'Sprawdź aktualny formularz i termin rozliczenia.'],
            'sources' => ['tax', 'tax-shares'], 'tool' => '/narzedzia.php?tool=oszczednosci',
        ],
        'ppk' => [
            'title' => 'PPK: wpłaty i wybory uczestnika', 'topic' => 'podatki', 'audience' => 'Konsument',
            'summary' => 'Jak działa podstawowa składka pracownika i pracodawcy oraz co sprawdzić w funduszu.',
            'takeaway' => 'Wpłata podstawowa pracownika wynosi zwykle 2%, a pracodawcy 1,5% wynagrodzenia; sprawdź wyjątki i aktualne zasady.',
            'sections' => [
                ['Skąd biorą się wpłaty', 'PPK to długoterminowy program, w którym oszczędności budują pracownik i pracodawca, a państwo przewiduje określone dopłaty po spełnieniu warunków. Pracownik może w pewnych sytuacjach obniżyć wpłatę podstawową, gdy jego wynagrodzenie mieści się w ustawowym limicie.'],
                ['Co kontrolować', 'Sprawdź w instytucji finansowej stan rachunku, fundusz zdefiniowanej daty, opłaty i politykę inwestycyjną. Wartość jednostek może się zmieniać. Zasady wypłaty przed 60. rokiem życia różnią się od wypłaty po osiągnięciu tego wieku.'],
            ], 'checks' => ['Zweryfikuj wysokość wpłat na pasku wynagrodzenia.', 'Sprawdź fundusz i koszty.', 'Przeczytaj zasady wypłaty środków.'],
            'sources' => ['ppk'], 'tool' => null,
        ],
        'stopy-procentowe' => [
            'title' => 'Stopy procentowe NBP i koszt pieniądza', 'topic' => 'rynek', 'audience' => 'Wszyscy',
            'summary' => 'Dlaczego decyzje RPP wpływają na nowe oferty kredytów, lokat i obligacji.',
            'takeaway' => 'Stopa referencyjna NBP to punkt odniesienia dla rynku; oprocentowanie Twojej umowy ma własną formułę i terminy aktualizacji.',
            'sections' => [
                ['Kanał wpływu', 'Rada Polityki Pieniężnej ustala podstawowe stopy NBP. Zmiana stopy referencyjnej wpływa na warunki krótkoterminowego rynku pieniężnego. Banki mogą zmieniać ceny nowych kredytów i depozytów, ale wpływ na istniejącą umowę zależy od jej konstrukcji.'],
                ['Czytaj warunki produktu', 'Przy kredycie zmiennoprocentowym sprawdź wskaźnik, marżę i datę przeliczenia raty. Przy lokacie sprawdź, czy oprocentowanie jest stałe przez cały okres. Aktualne stopy sprawdzaj w komunikatach NBP; liczby podane w historycznym materiale nie są bieżącą ofertą.'],
            ], 'checks' => ['Sprawdź najnowszą decyzję RPP.', 'Znajdź formułę oprocentowania w umowie.', 'Policz wpływ zmiany stopy na swój budżet.'],
            'sources' => ['nbp-rates', 'knf-s'], 'tool' => '/narzedzia.php?tool=rata',
        ],
        'inflacja-i-realny-zysk' => [
            'title' => 'Inflacja i realna wartość oszczędności', 'topic' => 'rynek', 'audience' => 'Wszyscy',
            'summary' => 'Nominalny zysk to nie to samo co wzrost siły nabywczej.',
            'takeaway' => 'Porównuj zysk po podatku z inflacją za ten sam okres; przybliżony realny wynik to (1 + zwrot) / (1 + inflacja) − 1.',
            'sections' => [
                ['Co mierzy CPI', 'GUS publikuje wskaźnik cen towarów i usług konsumpcyjnych na podstawie koszyka wydatków gospodarstw domowych. Twoja osobista inflacja może różnić się od wskaźnika ogółem, jeśli struktura Twoich wydatków jest inna.'],
                ['Realny wynik', 'Jeśli kapitał rośnie nominalnie, ale ceny rosną szybciej, siła nabywcza może maleć. Dla poprawnego porównania używaj stopy zwrotu po podatku i opłatach oraz inflacji z tego samego okresu. Prognozy inflacji są niepewne, więc kalkulacja przyszłego realnego wyniku jest scenariuszem.'],
            ], 'checks' => ['Ustal zysk po podatku i kosztach.', 'Użyj wskaźnika GUS dla tego samego okresu.', 'Traktuj przyszłą inflację jako założenie.'],
            'sources' => ['gus', 'tax'], 'tool' => '/narzedzia.php?tool=oszczednosci',
        ],
        'kursy-walut' => [
            'title' => 'Kursy walut: tabela NBP a koszt wymiany', 'topic' => 'rynek', 'audience' => 'Wszyscy',
            'summary' => 'Średni kurs NBP, kurs sprzedaży i spread służą innym celom.',
            'takeaway' => 'Przy realnej transakcji sprawdź kurs kupna lub sprzedaży, prowizję i termin księgowania.',
            'sections' => [
                ['Tabele NBP', 'NBP publikuje oficjalne tabele średnich kursów oraz tabele kursów kupna i sprzedaży. Kurs średni jest ważnym punktem odniesienia i bywa stosowany w rozliczeniach, ale zwykle nie jest kursem, po którym kupisz walutę w banku.'],
                ['Rzeczywisty koszt', 'Koszt wymiany to różnica kursów oraz możliwa prowizja. Przy płatności kartą znaczenie mogą mieć kurs organizacji płatniczej, banku i moment rozliczenia. Dla kredytu walutowego dodatkowo dochodzi ryzyko zmiany kursu.'],
            ], 'checks' => ['Sprawdź tabelę NBP i datę kursu.', 'Porównaj kurs transakcyjny i opłaty.', 'Uwzględnij spread i ryzyko walutowe.'],
            'sources' => ['nbp'], 'tool' => null,
        ],
        'plynnosc-firmy' => [
            'title' => 'Płynność finansowa firmy', 'topic' => 'firma', 'audience' => 'Profesjonalista',
            'summary' => 'Prosty rytm prognozowania wpływów, wydatków i luki finansowania.',
            'takeaway' => 'Zysk księgowy nie gwarantuje gotówki w terminie płatności zobowiązań.',
            'sections' => [
                ['Prognoza 13 tygodni', 'Zacznij od salda środków, zaplanowanych wpływów i wymagalnych płatności w każdym tygodniu. Oddziel wpływy pewne od opóźnionych i warunkowych. Aktualizuj prognozę co tydzień i śledź różnice między planem a wykonaniem.'],
                ['Dźwignie zarządcze', 'Negocjacja terminów, windykacja należności, limit obrotowy i faktoring mogą pomóc zlikwidować lukę gotówkową, ale każdy instrument ma koszt i ryzyko. Faktoring polega na finansowaniu należności z faktur; porównaj pełną cenę, regres i wpływ na relację z odbiorcą.'],
            ], 'checks' => ['Zrób prognozę tygodniową.', 'Zidentyfikuj największe przeterminowane należności.', 'Porównaj koszt i warunki finansowania.'],
            'sources' => ['parp'], 'tool' => null,
        ],
        'finansowanie-firmy' => [
            'title' => 'Finansowanie firmy: kredyt, leasing, faktoring', 'topic' => 'firma', 'audience' => 'Profesjonalista',
            'summary' => 'Dopasuj formę finansowania do aktywa, cyklu należności i źródła spłaty.',
            'takeaway' => 'Porównuj przepływy pieniężne w całym okresie, zabezpieczenia oraz wpływ na płynność.',
            'sections' => [
                ['Cel finansowania', 'Kredyt inwestycyjny może pasować do długotrwałego aktywa, a obrotowy do sezonowej luki. Leasing jest często związany z finansowaniem konkretnego środka trwałego. Faktoring uwalnia środki z należności handlowych. Nazwa produktu nie zastępuje analizy terminu i kosztu.'],
                ['Rzetelne porównanie', 'Ujednolić scenariusz kwoty i terminów przepływów. Sprawdź oprocentowanie, prowizje, opłaty administracyjne, wykup, zabezpieczenia i konsekwencje opóźnienia. Kwestie podatkowe i księgowe zależą od formy finansowania oraz sytuacji firmy.'],
            ], 'checks' => ['Rozpisz harmonogram gotówki.', 'Porównaj pełny koszt i zabezpieczenia.', 'Sprawdź warunki wypowiedzenia i ryzyko stopy.'],
            'sources' => ['parp'], 'tool' => null,
        ],
        'analiza-zadluzenia' => [
            'title' => 'Analiza zadłużenia i kosztu kapitału', 'topic' => 'firma', 'audience' => 'Profesjonalista',
            'summary' => 'Praktyczne wskaźniki dla zespołu finansowego i kredytowego.',
            'takeaway' => 'Wskaźnik jest sygnałem do analizy jakości przepływów, nie samodzielną decyzją kredytową.',
            'sections' => [
                ['Pokrycie obsługi długu', 'DSCR można ująć jako gotówkę dostępną na obsługę długu podzieloną przez raty kapitałowe i odsetki w tym samym okresie. Zdefiniuj konsekwentnie licznik i mianownik, ponieważ umowy finansowania mogą stosować własne definicje.'],
                ['Scenariusze', 'Oceń zmianę kosztu finansowania, spadek sprzedaży, wydłużenie rotacji należności i jednorazowe wydatki. Zwróć uwagę na termin zapadalności długu, kowenanty i bufor płynności. Przy porównaniu finansowania uwzględnij koszty dodatkowe i harmonogram wypłat.'],
            ], 'checks' => ['Zapisz definicje wskaźników.', 'Przetestuj kilka scenariuszy gotówkowych.', 'Sprawdź kowenanty i terminy zapadalności.'],
            'sources' => ['parp', 'knf'], 'tool' => null,
        ],
    ];
}
