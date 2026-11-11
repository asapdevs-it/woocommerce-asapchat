# Specyfikacja: wtyczka WordPress `asapchat`, wersja 1

Data: 2026-11-11. Status: zatwierdzona do realizacji.

Kontrakt z AsapChat jest gotowy i opisany w `docs/CONNECT-API.md`. Przeczytaj go w całości przed pierwszym zadaniem. Gdy ten dokument i kontrakt mówią co innego o polach lub kodach odpowiedzi, wygrywa kontrakt. Zgłoś wtedy różnicę w issue.

Zadania mają w tytule numer od 01 do 14 i ten numer wyznacza kolejność. Numer issue na GitHubie (`#N`) jest inny i nie mówi nic o kolejności. W dokumentach „zadanie 05” zawsze znaczy numer z tytułu.

## 1. Cel

Właściciel strony na WordPressie instaluje wtyczkę z katalogu WordPress.org, klika jeden przycisk i ma na stronie czat AsapChat oraz wyszukiwarkę AsapSearch (gdy sklep ma zaimportowane produkty). Bez wklejania kodu i bez edycji motywu. Na ekranie wtyczki ustawia też wygląd czatu, miejsca, w których widgety mają się nie pokazywać, i widzi, czy asystent ma dostęp do produktów i zamówień.

Miara sukcesu: od aktywacji wtyczki do widocznego czatu mija mniej niż 2 minuty u osoby, która ma już konto AsapChat.

## 2. Słownik

- **AsapChat**: usługa pod adresem `https://panel.asapchat.io`. Adres trzymasz w jednej stałej `ASAPCHAT_BASE`.
- **Token połączenia**: tajny ciąg: `ack_` i 48 znaków `[A-Za-z0-9]` (razem 52 znaki). Wtyczka przedstawia się nim usłudze. Dostaje go raz, przy połączeniu.
- **Klucz instalacyjny**: ten sam rodzaj tokenu, ale skopiowany ręcznie z panelu AsapChat. Ścieżka zapasowa.
- **Config**: odpowiedź JSON z `GET /api/plugin/v1/config`. Zawiera adresy skryptów, ich atrybuty, ustawienia wyglądu, statusy.
- **PKCE**: zabezpieczenie wymiany kodu. Wtyczka losuje sekret (`code_verifier`). Do panelu wysyła tylko jego skrót (`code_challenge`). Sekret pokazuje dopiero przy wymianie kodu na token.
- **Transient**: wpis tymczasowy w WordPressie z czasem ważności (`set_transient`).
- **HPOS**: nowszy sposób przechowywania zamówień w WooCommerce. Wtyczka deklaruje zgodność, chociaż nie dotyka zamówień.
- **Ustawienie lokalne**: wartość zapisana tylko w bazie WordPressa (`wp_options`). AsapChat jej nie zna.

## 3. Historyjki użytkownika

1. Mam konto AsapChat. Klikam „Połącz”, wybieram sklep i czat jest na stronie.
2. Nie mam konta. Klikam ten sam przycisk, zakładam konto, przechodzę kreator AsapChat i wracam do WordPressa automatycznie.
3. Przekierowanie u mnie nie działa. Wklejam klucz instalacyjny.
4. Wyłączam osobno czat, wyszukiwarkę albo dodawanie do koszyka z wyszukiwarki. Konto zostaje połączone.
5. Zmieniam nazwę asystenta, kolor, pozycję i powitania bez wychodzenia z WordPressa.
6. Ustawiam, po ilu sekundach czat ma się pojawić i na których stronach lub produktach ma go nie być.
7. Widzę, czy czat faktycznie działa na stronie i czy asystent zna moje produkty i zamówienia.
8. Rozłączam wtyczkę i mam pewność, że skrypty znikają ze strony.

## 4. Ekran i stany

Jedna strona: **Ustawienia > AsapChat** (`options-general.php?page=asapchat`). Uprawnienie: `manage_options`. Makieta wszystkich stanów: `docs/mockups/settings.html` (otwórz w przeglądarce, przełącz stan u góry). 

**Makieta a ten dokument.** O zachowaniu i tekstach decyduje ten dokument, o układzie i wyglądzie makieta. Tekst, który jest tylko w makiecie, obowiązuje wtedy, gdy wymienia go ten dokument (punkty 4.2 do 4.5 i tabela w punkcie 22). Wariant opisany tutaj, którego makieta nie pokazuje, budujesz według opisu, w stylu sąsiednich elementów makiety. Makieta ładuje font z Google Fonts i używa klas `mk-wp-`: to uproszczenia podglądu, nie kopiuj ich do wtyczki (zasady techniczne niżej).

### 4.1 Wygląd wspólny

Ekran wygląda jak panel AsapChat osadzony w panelu WordPressa. Menu boczne i górna belka WordPressa zostają bez zmian.

- **Nagłówek ekranu**: czarny pasek (`#161616`) z białym logo AsapChat (`admin/img/asapchat-logo-white.svg`, plik jest już w repo; wersja na jasne tło: `admin/img/asapchat-logo-dark.svg`), pod nim fioletowa linia 4 px (`#6543f0`).
- **Tło** `#fafafa`. **Karty** białe, obrys `#e9eaeb`, promień 16 px, cień `0 6px 20px rgba(15,26,23,.05)`.
- **Tekst** `#161616`, etykiety pól `#424448`, opisy `#56595f`.
- **Akcent** `#6543f0` z białym tekstem. Tło jasne akcentu `#f6f4fe`, mocniejsze `#ece8fd`.
- **Pola**: wysokość 44 px, promień 10 px, obrys `#d5d7da`. Fokus: obrys `#6543f0` i poświata 3 px `rgba(101,67,240,.2)`.
- **Przyciski**: główny fioletowy, wysokość 48 px. Na ekranie jest tylko jeden główny przycisk. Pozostałe są białe z obrysem.
- **Font** Open Sans (400, 600, 700, 800). Tytuł 30 px / 800, odstęp liter -0.02em. Tytuł karty 18 px / 800. Treść 14 do 15 px. Opisy 13 px.
- **Przełączniki** zamiast checkboxów. **Statusy** jako pigułki z kropką: zielona `#05c732` albo szara.
- Bez gradientów i bez emoji.

Zasady techniczne:

1. Każda klasa CSS zaczyna się od `asapchat-`. Każda reguła jest zagnieżdżona pod korzeniem `.asapchat-screen`. Styl nie może zmienić niczego poza ekranem wtyczki.
2. CSS i JS wtyczki ładujesz tylko na stronie `settings_page_asapchat` (sprawdź `$hook` w `admin_enqueue_scripts`).
3. Font leży w paczce: `admin/fonts/*.woff2` (podzbiory latin i latin-ext, żeby działały polskie znaki). Pliki bierzesz z pakietu npm `@fontsource/open-sans` (licencja SIL OFL 1.1), szczegóły w punkcie 21.3. Ładujesz go przez `@font-face` w `admin/admin.css`. Żadnych odwołań do Google Fonts ani innych zewnętrznych adresów w panelu admina.
4. Rozwijane bloki to natywny `<details>`. Kolor to natywny `<input type="color">` obok pola tekstowego z wartością `#rrggbb`. Własny JS w panelu to jeden krótki plik `admin/admin.js`. Robi dwie rzeczy: synchronizuje te dwa pola i aktualizuje liczniki znaków przy polach tekstowych karty „Wygląd” (np. „5 / 40”). Bez JS formularz działa tak samo, liczniki pokazują wtedy stan z chwili wczytania strony.
5. Ekran działa na szerokości 390 px (telefon) i 1280 px.

### 4.2 Stan „Niepołączony”

Kolejność elementów:

1. Nagłówek z logo.
2. Tytuł „Połącz sklep z AsapChat” i jedno zdanie: „Czat AI i wyszukiwarka produktów na Twojej stronie, bez wklejania kodu.”
3. Przycisk główny „Połącz z AsapChat”.
4. Pod przyciskiem: „Nie masz konta? Założysz je w następnym kroku, za darmo.”
5. Lista trzech kroków (numerowana): „Klikasz „Połącz z AsapChat”.”, „Logujesz się albo zakładasz konto i przechodzisz krótki kreator.”, „Wracasz tutaj automatycznie. Czat jest już na stronie.”
6. Informacja o zgodzie: „Po kliknięciu przejdziesz na panel.asapchat.io. Wtyczka łączy się z AsapChat dopiero od tego momentu.” oraz linki „Polityka prywatności” (`https://asapchat.io/polityka-prywatnosci`) i „Regulamin” (`https://asapchat.io/regulamin`), oba w nowej karcie.
7. Zwinięte „Mam klucz instalacyjny”: pole tekstowe i biały przycisk „Połącz kluczem”. Podpowiedź: „Klucz znajdziesz w panelu AsapChat: Sklep, Wtyczka, Klucz instalacyjny.”

**Wariant bez przycisku** (makieta go nie pokazuje). Przycisk „Połącz z AsapChat” jest nieaktywny (`disabled`), gdy zachodzi jedno z:

- `home_url()` nie zaczyna się od `https://` i host nie jest `localhost` ani `127.0.0.1`,
- `home_url()` i `admin_url()` mają różne hosty albo różne porty. Przy porównaniu hostów pomijasz początkowe `www.` (tak samo robi AsapChat).

Pokazujesz wtedy pod przyciskiem zdanie: „Połączenie przyciskiem wymaga adresu https i panelu na tej samej domenie co strona. Użyj klucza instalacyjnego.”, ukrywasz listę trzech kroków i rozwijasz pole klucza (`<details open>`).

### 4.3 Stan „Połączony”

Sekcje w tej kolejności. Każda sekcja od 2 do 6 to osobna karta.

**1. Nagłówek.** W czarnym pasku obok logo: link „Otwórz panel AsapChat” (`links.panel`, nowa karta) i „Rozłącz”. Pod paskiem: nazwa sklepu (`shop.name`) jako tytuł, pigułka z planem i adres strony. Plan: gdy `shop.ai_conversations_limit` jest liczbą (przy każdym planie), tekst „Plan {nazwa}, {limit} rozmów AI miesięcznie”, np. „Plan Free, 50 rozmów AI miesięcznie”. Gdy limit to `null`, sama nazwa: „Plan Pro”. Nazwa planu: `free` = „Free”, `pro` = „Pro”, inna wartość = wartość z dużej litery. „Rozłącz” wymaga potwierdzenia (`confirm` w przeglądarce wystarczy).

**2. Status.** Pod tytułem karty: „Sprawdzamy, czy widgety faktycznie pojawiły się na Twojej stronie.” Dwa wiersze:

| Wiersz | Warunek | Pigułka | Opis |
|---|---|---|---|
| Czat | `status.widget_seen_at` ma datę | zielona „Czat działa” | „Ostatnio widziany {data}” |
| Czat | `status.widget_seen_at` to `null` | szara „Jeszcze niewidziany na stronie” | „Otwórz sklep w nowej karcie, żeby sprawdzić.” z linkiem do `home_url()` |
| Wyszukiwarka | `status.search_seen_at` ma datę | zielona „Wyszukiwarka działa” | „Ostatnio widziana {data}” |
| Wyszukiwarka | `search.enabled` to `true`, brak daty | szara „Jeszcze niewidziana na stronie” | jak przy czacie (makieta nie pokazuje tego wariantu) |
| Wyszukiwarka | `search.enabled` to `false` | szara „Niedostępna” | „Najpierw podłącz produkty.” |

Daty przychodzą w ISO 8601 ze strefą (np. `2026-11-11T10:42:00+01:00`). Pokazujesz je w strefie i formacie witryny: `wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $value ) )`. Makieta pokazuje „dziś o 10:42” tylko dla przykładu, nie budujesz dat względnych.

**3. Widgety.** Pod tytułem karty: „Włącz albo wyłącz to, co ma być widoczne na stronie.” Trzy przełączniki. To ustawienia lokalne.

| Przełącznik | Opis pod nazwą | Domyślnie | Uwagi |
|---|---|---|---|
| Czat | „Okno rozmowy z asystentem AI na stronie.” | włączony | wyłączony = skrypt czatu nie trafia na stronę |
| Wyszukiwarka AsapSearch | „Zastępuje wyszukiwarkę sklepu szybką wyszukiwarką produktów.” | włączony | gdy `search.enabled` to `false`: przełącznik nieaktywny, zamiast opisu „Wyszukiwarka włączy się, gdy AsapChat zaimportuje Twoje produkty.” i link „Podłącz produkty” na `sources.connect_url` (nowa karta) |
| Dodawanie do koszyka z wyszukiwarki | „Przycisk „Do koszyka” od razu w wynikach wyszukiwania.” | włączony | nieaktywny, gdy wyszukiwarka jest wyłączona lub niedostępna; zamiast opisu „Dostępne razem z wyszukiwarką.” |

Nieaktywny przełącznik nie zmienia zapisanej wartości. Dzięki temu wyszukiwarka pojawi się sama po imporcie produktów.

**4. Wygląd.** Pola zapisywane w AsapChat przez `PATCH /api/plugin/v1/settings`. Wartości startowe bierzesz z `settings` w configu.

| Pole | Klucz | Reguła |
|---|---|---|
| Nazwa asystenta | `bot_name` | tekst, do 40 znaków |
| Kolor główny | `main_color` | `#rrggbb`, pole koloru i pole tekstowe |
| Pozycja | `position` | „Prawy dół” = `right-bottom`, „Lewy dół” = `left-bottom` |
| Powitanie 1 | `greeting_1` | tekst do 120 znaków, puste = bez dymka |
| Powitanie 2 | `greeting_2` | tekst do 120 znaków, puste = bez dymka |

Pod tytułem karty zdanie: „To te same ustawienia co w panelu AsapChat. Zmiana tutaj jest widoczna tam i odwrotnie.” Etykieta pola pozycji: „Pozycja na stronie”. Pola „Nazwa asystenta”, „Powitanie 1” i „Powitanie 2” mają licznik znaków w formacie „{wpisane} / {limit}” oraz atrybut `maxlength`.

Błąd przy polu (walidacja lokalna i `422`, makieta nie pokazuje tego wariantu): czerwony tekst 13 px (`#d92d20`) pod polem, obrys pola w tym samym kolorze, `aria-invalid="true"` i `aria-describedby` na polu. Teksty: „Nazwa asystenta może mieć do 40 znaków.”, „Podaj nazwę asystenta.”, „Podaj kolor w formacie #rrggbb, np. #6543f0.”, „Powitanie może mieć do 120 znaków.”, „Wybierz pozycję z listy.”

**5. Gdzie pokazywać.** Ustawienia lokalne.

| Pole | Reguła |
|---|---|
| Opóźnienie czatu | liczba sekund od 0 do 60, domyślnie 0, obok pola słowo „sekund”. Opis: „Po ilu sekundach od wejścia na stronę pojawia się czat. Od 0 do 60.” |
| Strony bez widgetów | wybór wielu stron z listy stron WordPressa |
| Produkty bez widgetów | wyszukiwarka produktów WooCommerce, wybór wielu. Pole widoczne tylko z aktywnym WooCommerce |
| Adres zawiera | pole wieloliniowe, jeden fragment adresu w linii, np. `/koszyk` albo `/kategoria-produktu/doniczki`. Do 20 linii, każda do 100 znaków. Opis: „Jeden fragment adresu w linii. Widgetów nie będzie na każdej stronie, której adres zawiera ten tekst.” |

Opis pod tytułem karty: „Na wybranych stronach i produktach nie pokażemy ani czatu, ani wyszukiwarki.”

Jak zbudować wybór: strony pobierasz przez `get_pages()` i pokazujesz jako `<select multiple>`. Produkty: `<select class="wc-product-search" multiple data-action="woocommerce_json_search_products">` i dołączony skrypt WooCommerce `wc-enhanced-select`. Nie pisz własnej wyszukiwarki produktów. Całą kategorię produktów wyklucza się fragmentem adresu. Makieta pokazuje wybrane pozycje jako „chipy” z polem tekstowym: to tylko docelowy wygląd. Obowiązują kontrolki opisane tutaj, ostylowane tak, żeby były możliwie bliskie makiecie.

**6. Źródło zamówień i produktów.** Zdanie pod tytułem: „Dzięki temu asystent zna Twoje produkty i odpowiada na pytania o status zamówienia.”

- Dwa wiersze statusu: „Produkty” (`sources.products_connected`) i „Zamówienia” (`sources.orders_connected`). Pigułka zielona „Podłączone” albo szara „Niepodłączone”.
- Biały przycisk „Podłącz w AsapChat” prowadzi na `sources.connect_url` w nowej karcie. Pod nim: „W panelu AsapChat kliknij Połącz z WooCommerce. Zobaczysz ekran zgody WooCommerce, zatwierdzasz i gotowe. Nie kopiujesz żadnych kluczy.”
- Rozwijane „Jak wygenerować klucze API w WooCommerce” (droga ręczna, gdy połączenie jednym kliknięciem się nie uda):
  1. Wejdź w WooCommerce, Ustawienia, Zaawansowane, REST API.
  2. Kliknij „Dodaj klucz”.
  3. Wpisz opis, np. „AsapChat”. Uprawnienia: „Odczyt”.
  4. Kliknij „Wygeneruj klucz API”.
  5. Skopiuj Consumer key i Consumer secret. Wklej je w panelu AsapChat po kliknięciu „Podłącz w AsapChat”. Secret jest widoczny tylko raz.
- Link w kroku 1 prowadzi na `admin.php?page=wc-settings&tab=advanced&section=keys`.
- Bez WooCommerce: instrukcję kluczy ukrywasz. Zostają statusy, przycisk i zdanie: „Nie masz WooCommerce? Asystent korzysta wtedy z bazy wiedzy, którą uzupełnisz w panelu AsapChat.”

Wtyczka sama nie tworzy kluczy API i nie wysyła ich do AsapChat. Pole `sources.systems` z configu w wersji 1 pomijasz (kontrakt na to pozwala).

**Zapis.** Pod kartami jeden główny przycisk „Zapisz zmiany”. Zapisuje sekcje 3, 4 i 5 jednym formularzem (opis w punkcie 9). Obok przycisku zdanie: „Wygląd zapisujemy na Twoim koncie AsapChat. Pozostałe ustawienia zostają w WordPressie.”

**Brak paska aktualizacji.** Wtyczkę aktualizuje WordPress.org. Dla platformy `wordpress` pole `plugin.latest_version` zawsze przychodzi jako `null` (tak mówi kontrakt), więc wtyczka nie buduje paska „Dostępna wersja” i nie czyta sekcji `plugin`. Nie ma własnego mechanizmu aktualizacji ani komunikatu o nowej wersji.

### 4.4 Stan „Błąd, połącz ponownie”

Pokazywany, gdy AsapChat odpowiedział `401`, `409` albo `410` (punkt 10). Skrypty nie są wtedy wstawiane.

| Kod | Tytuł | Opis |
|---|---|---|
| `401` | „Połączenie z AsapChat zostało przerwane” | „Połączenie odwołano w panelu AsapChat albo klucz instalacyjny wygasł. Połącz stronę ponownie, żeby przywrócić czat.” |
| `409` | jak wyżej | „To połączenie jest przypisane do innej domeny. Połącz tę stronę ponownie.” |
| `410` | jak wyżej | „Ten sklep został usunięty w AsapChat. Połącz stronę z innym sklepem.” |

Kolejność elementów jak w makiecie:

1. Czerwony komunikat nad kartą: „Czat i wyszukiwarka nie są teraz dodawane do strony.”
2. Tytuł i opis z tabeli.
3. Przycisk główny „Połącz ponownie”. Uruchamia ten sam przepływ co „Połącz” (z tym samym wariantem bez przycisku z punktu 4.2).
4. Zdanie: „Twoje ustawienia widgetów i wykluczeń zostały zachowane.”
5. Zwinięte „Mam klucz instalacyjny”, jak w stanie „Niepołączony”.

### 4.5 Komunikaty

Komunikat pokazujesz raz, w ekranie wtyczki, nad kartami. Da się go zamknąć. Nie pokazujesz komunikatów wtyczki na innych stronach panelu.

| Sytuacja | Tekst |
|---|---|
| Połączono | „Połączono ze sklepem {nazwa}. Czat jest już na Twojej stronie.” |
| Kliknięto „Anuluj” w panelu | „Połączenie anulowane. Nic nie zostało zmienione.” |
| Zły lub brakujący `state` | „Sesja łączenia wygasła. Kliknij Połącz ponownie.” |
| Wymiana kodu nieudana | „Nie udało się połączyć. Spróbuj ponownie albo użyj klucza instalacyjnego.” |
| Zapisano | „Zapisano zmiany.” |
| Wygląd niezapisany (sieć, `429`, `5xx`) | „Ustawienia w WordPressie zapisane. Wyglądu nie udało się zapisać w AsapChat, spróbuj ponownie za chwilę.” |
| Nieudane odświeżenie configu | „Nie udało się odświeżyć ustawień z AsapChat. Czat działa dalej na zapisanych ustawieniach.” |
| Rozłączono | „Rozłączono. Czat i wyszukiwarka zniknęły ze strony.” |

## 5. Co jest zapisane gdzie

### 5.1 Lokalnie, w `wp_options`

| Opcja | Zawartość | autoload |
|---|---|---|
| `asapchat_connection` | `token`, `shop_id`, `shop_name`, `connected_at`, `status` (`ok` albo `error`), `error_code` (401, 409, 410 albo brak) | no |
| `asapchat_config` | ostatnia poprawna odpowiedź `GET /config` w całości oraz `fetched_at` | yes, czytana na każdej stronie sklepu |
| `asapchat_settings` | `chat_enabled`, `search_enabled`, `search_add_to_cart` (bool), `delay_seconds` (0 do 60), `excluded_ids` (lista ID stron i produktów), `excluded_url_parts` (lista tekstów) | yes |

Transienty:

- `asapchat_connect_{user_id}`: `state` i `code_verifier`. Czas życia **2 godziny** (`2 * HOUR_IN_SECONDS`). Nie skracaj. Nowy użytkownik przechodzi po drodze rejestrację i cały kreator AsapChat.
- `asapchat_form_{user_id}`: wpisane wartości pól wyglądu, gdy zapis w AsapChat się nie udał. Czas życia 60 sekund. Dzięki temu formularz nie gubi tego, co wpisał użytkownik.

Token nigdy nie trafia do `asapchat_config`, do HTML, do JS ani do logów.

### 5.2 W AsapChat

- Wygląd czatu: `bot_name`, `main_color`, `position`, `greeting_1`, `greeting_2`. Wtyczka trzyma tylko kopię w `asapchat_config`.
- Plan i limit rozmów, nazwa sklepu.
- Stan źródeł (`sources`) i daty `status.*_seen_at`.
- Klucze API WooCommerce. Wtyczka nigdy ich nie widzi.
- Baza wiedzy, rozmowy, statystyki. Tego na ekranie wtyczki nie ma.

## 6. Przepływ „Połącz”

Pola i odpowiedzi: `docs/CONNECT-API.md`, punkt 2.

1. Formularz POST do `admin-post.php` z `action=asapchat_connect_start` i nonce.
2. Handler sprawdza `check_admin_referer( 'asapchat_connect' )` i `current_user_can( 'manage_options' )`.
3. Losuje `state` (`wp_generate_password( 32, false )`) i `code_verifier` (wzór w kontrakcie). Zapisuje oba w transiencie `asapchat_connect_{user_id}` na 2 godziny.
4. Liczy `code_challenge = base64url( sha256( code_verifier ) )`, bez znaków `=`. Wynik ma dokładnie 43 znaki.
5. Przekierowuje przez `wp_redirect` na `{ASAPCHAT_BASE}/connect` z parametrami: `platform=wordpress`, `site_url` = `home_url()`, `return_url` = `admin_url( 'admin-post.php?action=asapchat_connect_callback' )`, `state`, `code_challenge`.
6. Użytkownik zatwierdza w panelu. Panel wraca na `return_url` z `code` i `state` albo z `error=access_denied` i `state`.
7. Handler `admin_post_asapchat_connect_callback`:
   1. `current_user_can( 'manage_options' )`, inaczej `wp_die`.
   2. Czyta transient. Porównuje `state` przez `hash_equals`. Brak transientu albo niezgodność: komunikat „Sesja łączenia wygasła”, koniec.
   3. Usuwa transient. `state` działa tylko raz.
   4. `error` w adresie: komunikat „Połączenie anulowane”, koniec.
   5. `POST /api/plugin/v1/token` z `code` i `code_verifier`.
   6. Odpowiedź `200`: zapisuje `asapchat_connection` (token, sklep, `status = ok`) i `asapchat_config` (pole `config` z odpowiedzi). Gdy `asapchat_settings` nie istnieje, zapisuje wartości domyślne. Planuje odświeżanie (punkt 10). Przekierowuje na ekran wtyczki z komunikatem sukcesu.
   7. Inna odpowiedź: komunikat błędu, nic nie zapisuje. Kod `code` przepada po pierwszej próbie, więc nie ponawiaj wymiany.
8. Callback nie ma nonce, bo wraca z zewnątrz. Chronią go: `state`, sesja zalogowanego administratora i PKCE.

### 6.1 Nowy użytkownik

Dla wtyczki to ten sam przepływ. Różnica jest tylko po stronie AsapChat:

1. Użytkownik klika „Połącz z AsapChat”.
2. W panelu wybiera „Załóż darmowe konto”.
3. Przechodzi kreator AsapChat: rejestracja, kod z maila, kolejne kroki.
4. Po kreatorze panel sam pokazuje ekran zgody.
5. Po kliknięciu „Połącz” użytkownik wraca do WordPressa i widzi stan „Połączony”.

Co z tego wynika dla kodu:

- Transient żyje 2 godziny. Po tym czasie powrót kończy się komunikatem „Sesja łączenia wygasła”.
- Powrót musi nastąpić w tej samej przeglądarce i na tym samym koncie WordPressa, które zaczęło łączenie.
- Drugie kliknięcie „Połącz” nadpisuje transient. Starsza karta z wcześniejszym `state` skończy się komunikatem o wygasłej sesji. To poprawne zachowanie.

## 7. Klucz instalacyjny (ścieżka zapasowa)

1. Użytkownik kopiuje klucz z panelu AsapChat (Sklep, Wtyczka, „Klucz instalacyjny”) i wkleja go w pole.
2. Handler `admin_post_asapchat_connect_key`: nonce, uprawnienie, `sanitize_text_field`. Klucz musi pasować do `^ack_[A-Za-z0-9]{48}$`. Inaczej: „To nie wygląda na klucz instalacyjny.”
3. `GET /api/plugin/v1/config` z `Authorization: Bearer {klucz}` i `X-Site-Url: {home_url()}`.
4. `200`: zapis jak w kroku 7.6 przepływu „Połącz”. Nazwę i ID sklepu bierzesz z `shop` w configu.
5. `401`: „Klucz jest nieprawidłowy, odwołany albo wygasł. Klucz trzeba użyć w ciągu 24 godzin od wygenerowania.” `409`: „Ten klucz jest już przypisany do innej domeny.” Inne błędy: „Nie udało się połączyć. Spróbuj ponownie za chwilę.”

## 8. Wstawianie skryptów na stronie

Robi to klasa `Asapchat_Frontend` na `wp_enqueue_scripts`. Front czyta tylko zapisane opcje. Nigdy nie wysyła żądania do AsapChat.

### 8.1 Kiedy wstawiamy

Wszystkie warunki muszą być spełnione:

- `asapchat_connection.status` to `ok`,
- blok w `asapchat_config` (`chat` albo `search`) ma `enabled = true` i `src` zaczyna się od `https://asapchat.io/`. Inny adres: skryptu nie wstawiasz. Ta reguła nie ma wyjątków, także gdy stała `ASAPCHAT_BASE` jest nadpisana,
- przełącznik w `asapchat_settings` jest włączony,
- to nie jest panel admina, podgląd Customizera, żądanie REST ani AJAX,
- bieżąca strona nie jest wykluczona (punkt 8.3).

### 8.2 Jak budujemy tag

1. `wp_enqueue_script( 'asapchat-chat', $config['chat']['src'], array(), null, true )` (stopka). Wyszukiwarka: `asapchat-search`, w `<head>`.
2. Filtr `script_loader_tag` dla tych dwóch uchwytów zwraca cały tag zbudowany od nowa. Dopisujesz **każdą** parę z `attributes`, bez listy dozwolonych nazw. Nazwy atrybutów mogą się zmieniać, nie wpisuj ich w kodzie na sztywno.
3. Wartość `true` daje atrybut bez wartości (np. `defer`). Pozostałe wartości przez `esc_attr`, `src` przez `esc_url`.
4. Nazwę atrybutu, która nie pasuje do `^[a-zA-Z_][a-zA-Z0-9_\-]*$`, pomijasz. To kontrola składni HTML, nie lista dozwolonych nazw.
5. Do obu tagów dodajesz `data-no-optimize="1"` i `data-cfasync="false"`. Wtyczki cache nie łączą wtedy skryptu z innymi. Widget czyta własne atrybuty z tagu.
6. **Dodawanie do koszyka.** Gdy `search_add_to_cart` jest wyłączone, pomijasz atrybut `allow_add-to-cart` z `search.attributes`. To jedyny wyjątek od zasady z kroku 2.

### 8.3 Wykluczenia

Na wykluczonej stronie nie wstawiasz żadnego skryptu, ani czatu, ani wyszukiwarki. Strona jest wykluczona, gdy zachodzi jedno z:

- `is_singular()` i `get_queried_object_id()` jest na liście `excluded_ids`,
- `is_shop()` (strona sklepu WooCommerce) i `wc_get_page_id( 'shop' )` jest na liście. Uwaga: strona sklepu to archiwum, więc `get_queried_object_id()` nie zwraca jej ID,
- adres żądania (`$_SERVER['REQUEST_URI']` po `wp_unslash` i `esc_url_raw`) zawiera którykolwiek tekst z `excluded_url_parts`. Porównanie bez rozróżniania wielkości liter (`stripos`).

Decyzja zapada w PHP przy generowaniu strony. Dzięki temu działa też ze stronami zapisanymi w cache, bo każdy adres ma własną kopię.

### 8.4 Opóźnienie czatu

Dotyczy tylko czatu. Wyszukiwarka ładuje się zawsze od razu.

- `delay_seconds = 0`: zwykły tag, jak w 8.2.
- `delay_seconds > 0`: nie wstawiasz tagu czatu. Zamiast tego dołączasz lokalny plik `public/asapchat-delay.js` (stopka) i przekazujesz mu dane przez `wp_add_inline_script( 'asapchat-delay', 'window.asapchatDelay = ' . wp_json_encode( $data ) . ';', 'before' )`. `$data` to `src`, `attributes` i `delay`.
- Plik po `delay` sekundach tworzy element `<script>`, ustawia te same atrybuty co w 8.2 (wartość `true` jako pusty atrybut) i dopina go do `<body>`. Plik jest krótki, czytelny, bez minifikacji.

Poza tym wtyczka nie dodaje na froncie niczego: żadnego CSS i żadnego innego JS.

## 9. Zapis formularza

Handler `admin_post_asapchat_save`: nonce, `manage_options`.

1. **Ustawienia lokalne** (sekcje Widgety i Gdzie pokazywać) czyścisz i zapisujesz zawsze: przełączniki jako bool, `delay_seconds` przez `absint` z przycięciem do 0..60, `excluded_ids` przez `absint` (tylko ID istniejących wpisów typu `page` lub `product`), `excluded_url_parts` przez `sanitize_text_field` linia po linii, puste linie odrzucasz.
2. **Wygląd.** Porównujesz przesłane pola z `asapchat_config['settings']`. Gdy nic się nie zmieniło, nie wysyłasz żądania.
3. Sprawdzasz reguły z tabeli w 4.3 jeszcze w PHP (długości licz przez `mb_strlen`, kolor przez `^#[0-9a-fA-F]{6}$`). Błąd pokazujesz przy polu, bez żądania.
4. Wysyłasz `PATCH /api/plugin/v1/settings` tylko ze zmienionymi polami, jako JSON (`wp_json_encode` i nagłówek `Content-Type: application/json`). Metoda to wyłącznie `PATCH`.
5. `200`: odpowiedź to pełny config. Zapisujesz go od razu w `asapchat_config`. Komunikat „Zapisano zmiany.”
6. `422`: odpowiedź ma kształt `{"message": "...", "errors": {"main_color": ["..."]}}`. Klucze `errors` to nazwy pól. Przy każdym polu z `errors` pokazujesz własny tekst wtyczki z punktu 4.3 (komunikaty z odpowiedzi są ogólne, nie wyświetlasz ich). Wpisane wartości zostają w formularzu (transient `asapchat_form_{user_id}`).
7. `401`, `409`, `410`: jak w punkcie 10, przechodzisz do stanu „Błąd”.
8. `429`, `5xx`, brak sieci: komunikat o niezapisanym wyglądzie, wpisane wartości zostają w formularzu. Ustawienia lokalne są już zapisane.

## 10. Odświeżanie configu i błędy

Kiedy pobierasz `GET /config`:

- Przy każdym otwarciu ekranu wtyczki w stanie „Połączony”.
- Co `refresh_after` sekund (pole z configu, zwykle 86400) przez WP-Cron. Zdarzenie jednorazowe `asapchat_refresh_config`, planowane od nowa po każdym pobraniu. Usuwane przy rozłączeniu i dezaktywacji.
- **Nigdy** podczas żądania strony sklepu.

Każde wywołanie z tokenem (`GET /config`, `PATCH /settings`, `DELETE /connection`) ma nagłówki `Authorization: Bearer {token}`, `Accept: application/json`, `X-Site-Url: {home_url()}`. `POST /token` idzie bez `Authorization` i bez `X-Site-Url` (tokenu jeszcze nie ma), z `Content-Type: application/json`. Timeout 5 sekund.

| Odpowiedź | Co robi wtyczka |
|---|---|
| `200` | Nadpisuje `asapchat_config`, ustawia `fetched_at`, planuje następne odświeżenie |
| `401`, `410` | Kasuje token i `asapchat_config`, usuwa zdarzenie crona, zapisuje `status = error` i `error_code`. Skrypty znikają ze strony. Ekran pokazuje stan 4.4 |
| `409` | Jak wyżej, z opisem o innej domenie |
| `400` | Brak `X-Site-Url`, czyli błąd w kodzie wtyczki. Na produkcji traktujesz jak `5xx` (nic nie kasujesz). Test automatyczny pilnuje, że nagłówek jest w każdym wywołaniu z tokenem |
| `200` z `v` innym niż `1` | Nie nadpisujesz zapisanej kopii. Komunikat: „Zaktualizuj wtyczkę AsapChat, żeby pobrać nowe ustawienia.” |
| `429`, `5xx`, timeout, brak sieci | Nic nie kasuje. Skrypty działają dalej na zapisanej kopii. Na ekranie komunikat „Nie udało się odświeżyć ustawień”. Następna próba przy kolejnym odświeżeniu |

`asapchat_settings` zostają w każdym z tych przypadków.

## 11. Rozłączenie, dezaktywacja, usunięcie

- **Rozłącz**: `DELETE /api/plugin/v1/connection`. Niezależnie od odpowiedzi usuwasz `asapchat_connection`, `asapchat_config` i zdarzenie crona. `asapchat_settings` zostają. Ekran wraca do stanu „Niepołączony”.
- **Dezaktywacja**: usuwasz zdarzenie crona. Dane zostają. Ponowna aktywacja przywraca stan i planuje odświeżanie.
- **Usunięcie** (`uninstall.php`): próba `DELETE` (timeout 5 s, błąd sieci nie blokuje usunięcia), potem usunięcie wszystkich opcji i transientów `asapchat_*`. Na multisite pętla po `get_sites()`.

## 12. Multisite

Każda witryna w sieci ma własne połączenie i własne opcje. Nie ma ustawień na poziomie sieci. Aktywacja sieciowa jest dozwolona. Daje tylko stronę ustawień w każdej witrynie.

## 13. WooCommerce

- Wtyczka nie wymaga WooCommerce. Czat ma sens na każdej stronie. Nie dodajesz nagłówka `Requires Plugins`.
- Bez WooCommerce znikają: pole „Produkty bez widgetów” i instrukcja kluczy API. Reszta działa.
- W `asapchat.php` deklaracja HPOS: `FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true )` na `before_woocommerce_init`.
- Nagłówek `WC tested up to`.
- Wtyczka nie czyta zamówień ani produktów i nie wysyła ich do AsapChat. Robi to AsapChat przez REST API WooCommerce, po zgodzie właściciela w panelu.

## 14. Zasady WordPress.org

Te zasady obowiązują w każdym zadaniu. Pełna checklista zgłoszenia: `docs/marketplace-listing.md`.

1. **Zero żądań do zewnętrznych adresów przed zgodą.** Świeżo aktywowana wtyczka nie wysyła niczego. Pierwsze żądanie pada po kliknięciu „Połącz” albo po wysłaniu klucza.
2. **Sekcja „External services” w `readme.txt`.** Opisuje, co, kiedy i dokąd wysyła wtyczka, z linkami do regulaminu i polityki prywatności.
3. **Żadnych zdalnych fontów ani zasobów w panelu admina.** Open Sans i logo leżą w paczce. Jedyne zdalne zasoby to skrypty widgetów na froncie, spod adresów z configu.
4. **GPL.** Kod i grafiki na licencji GPLv2 lub późniejszej. Open Sans ma licencję zgodną z GPL. Dopisz ją w `readme.txt`.
5. **Plugin Check** bez błędów w kategorii „Plugin Repo”.
6. Brak własnego mechanizmu aktualizacji. Brak iframe w panelu. Kod czytelny, bez minifikacji i zaciemniania.
7. Komunikaty tylko na stronie wtyczki, zamykane.

Ryzyko: zespół WordPress.org może poprosić o poprawki albo odrzucić zgłoszenie (np. z powodu oznaczenia marki w widgecie). Wtedy poprawiamy i wgrywamy ponownie. To nie wstrzymuje prac nad kodem.

## 15. Tłumaczenia

Teksty źródłowe po angielsku, przez `__()` / `esc_html__()` z domeną `asapchat`. W repo `languages/asapchat.pot` i komplet `pl_PL`. Komplet tekstów w obu językach jest w punkcie 22: tekst angielski wpisujesz w kodzie dokładnie tak, jak tam, polski trafia do `pl_PL`. Forma „Ty”, krótko, bez pauz (długi myślnik, U+2014).

## 16. Bezpieczeństwo (checklista do review)

- [ ] Brak żądań do AsapChat przed kliknięciem „Połącz” lub wysłaniem klucza (test automatyczny: `wp_remote_*` nie jest wołane ani razu, punkt 21.2).
- [ ] Nonce i `manage_options` na każdej akcji `admin-post` poza callbackiem. Callback: `manage_options` i `state` przez `hash_equals`.
- [ ] `state` i `code_verifier` jednorazowe. Transient kasowany przed wymianą kodu.
- [ ] Adres AsapChat w jednej stałej `ASAPCHAT_BASE`. Nadpisanie tylko przez `define` w `wp-config.php`.
- [ ] Token nie pojawia się w HTML, w komunikatach, w logach, w adresie URL ani w JS.
- [ ] Wszystkie wartości z configu escapowane przy wyświetlaniu. `src` spoza `https://asapchat.io/` nie jest renderowany.
- [ ] Wszystkie pola formularza czyszczone przed zapisem (punkt 9).
- [ ] Brak endpointów REST, akcji AJAX `nopriv` i parametrów diagnostycznych.
- [ ] `uninstall.php` sprawdza `WP_UNINSTALL_PLUGIN`.
- [ ] CSS i JS panelu ładowane tylko na stronie wtyczki.

## 17. Macierz testów

| Oś | Warianty |
|---|---|
| WordPress | najnowszy, najnowszy minus 2 wersje główne |
| PHP | 7.4, 8.1, 8.3 (te same wersje co w CI). 8.4: jedno przejście scenariuszy 1, 2 i 10 przed wydaniem |
| WooCommerce | brak; najnowszy z włączonym HPOS |
| Motyw | Storefront, motyw blokowy (Twenty Twenty-Five), Astra |
| Cache | WP Super Cache, LiteSpeed Cache, W3 Total Cache, Autoptimize (darmowe, każda z włączonym łączeniem lub opóźnianiem JS tam, gdzie je ma). WP Rocket jest płatny: testujesz go tylko wtedy, gdy masz licencję, nie jest wymagany |
| Zgody | jedna wtyczka cookies (np. Complianz) aktywna |
| Sieć | pojedyncza witryna, multisite (2 witryny, różne sklepy AsapChat). Plan Free to jeden sklep na konto, więc drugą witrynę łączysz z drugim kontem AsapChat założonym na inny adres e-mail |
| Szerokość ekranu wtyczki | 390 px, 1280 px |

Wszystkie scenariusze przechodzisz na kombinacji podstawowej (najnowszy WP, PHP 8.3, WooCommerce, Storefront). Na pozostałych wyrywkowo: 1, 2, 6, 10, 16.

Konto testowe: `docs/CONNECT-API.md`, punkt 7. Lokalny sklep krok po kroku: punkt 21.4.

Na `localhost` nie uzyskasz `search.enabled = true` ani podłączonych źródeł, bo AsapChat nie sięgnie do API Twojego sklepu. Dlatego scenariusze 9, 19 (część o wyszukiwarce) oraz stan „Podłączone” w scenariuszu 14 i działającą wyszukiwarkę przechodzisz na sklepie wystawionym pod publicznym adresem `https` (tunel, punkt 21.4). W testach automatycznych używasz przykładu z kontraktu, punkt 7a. Scenariusz 3 wymaga za każdym razem nowego adresu e-mail.

| Nr | Scenariusz | Oczekiwany wynik |
|---|---|---|
| 1 | Aktywacja | Zero żądań wychodzących. Brak błędów PHP przy `WP_DEBUG` |
| 2 | Połącz przyciskiem, istniejące konto | Stan „Połączony”. Czat na stronie głównej, produktowej i w koszyku |
| 3 | Połącz przyciskiem, nowe konto | Rejestracja, kreator, ekran zgody, powrót do WordPressa bez ręcznych kroków |
| 4 | „Anuluj” w panelu | Komunikat o anulowaniu. Nic nie zapisane |
| 5 | Powrót ze złym `state` i po ponad 2 godzinach | Komunikat o wygasłej sesji. Bez wymiany kodu |
| 6 | Klucz instalacyjny | Stan „Połączony”. Zły klucz: czytelny błąd |
| 7 | Przełącznik czatu wyłączony | Brak tagu czatu. Wyszukiwarka zostaje |
| 8 | `search.enabled = false` | Przełącznik wyszukiwarki nieaktywny z linkiem. Brak tagu wyszukiwarki |
| 9 | Dodawanie do koszyka wyłączone | Tag wyszukiwarki bez atrybutu `allow_add-to-cart` |
| 10 | Zmiana nazwy, koloru, pozycji, powitań | Wartości widoczne w panelu AsapChat. Zmiana w panelu widoczna we wtyczce po otwarciu ekranu |
| 11 | Zły kolor, za długa nazwa | Błąd przy polu. Wpisane wartości nie znikają |
| 12 | Opóźnienie 5 s | W źródle strony brak tagu czatu. Czat pojawia się po 5 sekundach z poprawnymi atrybutami |
| 13 | Wykluczona strona, wykluczony produkt, strona sklepu, fragment adresu | Na tych adresach brak obu skryptów. Na pozostałych są |
| 14 | Źródła | Statusy zgodne z panelem. Przycisk otwiera `connect_url`. Bez WooCommerce brak instrukcji kluczy |
| 15 | Odwołanie połączenia w panelu (`401`) | Stan „Błąd”. Brak tagów. Ustawienia lokalne zostają |
| 16 | Brak sieci do AsapChat | Tagi zostają. Komunikat o nieudanym odświeżeniu |
| 17 | Rozłącz | Brak tagów. Stan „Niepołączony” |
| 18 | Usunięcie wtyczki | `wp option list --search='asapchat*'` jest pusty |
| 19 | Cache z łączeniem JS | Czat działa i ma poprawne atrybuty w źródle strony |
| 20 | Ekran wtyczki na 390 px i 1280 px | Nic nie wychodzi poza ekran. Styl wtyczki nie zmienia menu ani innych stron panelu |
| 21 | Panel admina, karta Sieć w przeglądarce | Żadnych żądań do cudzych domen (fonty i logo z paczki) |

## 18. Definicja ukończenia

- Wszystkie zadania z punktu 20 zamknięte. CI zielone: PHPCS, PHPUnit, Plugin Check bez błędów.
- Wszystkie sekcje ekranu z punktu 4 działają i wyglądają jak w makiecie, na 390 px i 1280 px.
- Macierz testów przejścia udokumentowana (tabela i zrzuty) w zadaniu 13.
- `readme.txt` przechodzi walidator WordPress.org i zawiera sekcję „External services”.
- Panel admina nie pobiera niczego z zewnętrznych adresów.
- Paczka zip poniżej 1 MB (z fontami), bez plików deweloperskich (`.git`, `tests`, `vendor`, `.ai`, `docs`).
- Przypadki z `docs/CONNECT-API.md`, punkt 7.5, przeklikane przed wydaniem.
- Opiekun repozytorium zatwierdził PR wydania.

## 19. Ustalenia

1. **Różne hosty `home_url()` i `admin_url()`** albo strona bez https: tylko klucz instalacyjny (punkt 4.2).
2. **Slug.** W formularzu zgłoszenia wpisujesz prośbę o slug `asapchat`. Zapasowy: `asapchat-ai-chat`.
3. **„Tested up to”.** Wpisujesz wersję aktualną w dniu wydania.
4. **Wtyczki cache.** `data-no-optimize` działa w LiteSpeed Cache (i w WP Rocket). Dla Autoptimize dodajesz filtr wykluczenia, jeśli scenariusz 19 tego wymaga.
5. **Kategorie produktów** nie mają osobnego pola wykluczeń. Wyklucza się je fragmentem adresu.

## 20. Zadania

Kolejność wykonania wyznacza numer w tytule (01 do 14), nie numer issue na GitHubie. Każde zadanie to jeden PR. Zadanie bierzesz dopiero wtedy, gdy wszystkie zadania z jego sekcji „Zależności” są zmergowane. Etykiety pochodzą z taksonomii w `SDLC.md`.

| Zadanie | Issue na GitHubie | Zależy od |
|---|---|---|
| 01 Szkielet wtyczki, CI i zasoby lokalne | #2 | brak |
| 02 Ekran wtyczki: rama, nagłówek i trzy stany | #3 | 01 |
| 03 Klient API i zapis danych | #4 | 01 |
| 04 Połącz przyciskiem | #5 | 02, 03 |
| 05 Klucz instalacyjny | #6 | 02, 03 |
| 06 Skrypt czatu i przełącznik „Czat” | #7 | 04 |
| 07 Wyszukiwarka i dodawanie do koszyka | #8 | 06 |
| 08 Sekcja „Wygląd” i zapis przez PATCH /settings | #14 | 02, 03, 04 |
| 09 Sekcja „Gdzie pokazywać”: opóźnienie i wykluczenia | #15 | 06, 07 |
| 10 Sekcje „Status” i „Źródło zamówień i produktów” | #16 | 02, 04 |
| 11 Odświeżanie configu, błędy i rozłączenie | #9 | 03, 04, 06 |
| 12 Usunięcie wtyczki, multisite, HPOS | #10 | 11 |
| 13 Tłumaczenia, readme.txt, grafiki i macierz testów | #11 | 01 do 12 |
| 14 Wydanie 1.0.0 i zgłoszenie do WordPress.org | #13 | 13 |

Zadania 02 i 03 oraz 04 i 05 można robić równolegle (dwóch deweloperów).

### 01 Szkielet wtyczki, CI i zasoby lokalne

Etykiety: `feature`, `priority-high`, `risk-low`, `skip-qa`.

- [ ] Stary prototyp jest usunięty z `main`: katalogi `admin/` (poza `admin/img/`), `includes/`, `public/`, `languages/` oraz pliki `woocommerce-asapchat.php`, `class-asapchat.php`, `index.php`, `uninstall.php`. Nie przenosisz z nich kodu. `LICENSE.txt` zostaje.
- [ ] `README.txt` zmienia nazwę na `readme.txt` (`git mv -f README.txt readme.txt`, na macOS zwykłe `git mv` nie zadziała) i dostaje treść szkicu z `docs/marketplace-listing.md`, punkt 8.
- [ ] Struktura katalogów z `AGENTS.md`. Plik główny to `asapchat.php` z nagłówkiem wtyczki, stałymi i deklaracją HPOS.
- [ ] Narzędzia z punktu 21 działają lokalnie i w GitHub Actions: `composer install`, `vendor/bin/phpcs`, `vendor/bin/phpunit`, Plugin Check przez `wp-env`.
- [ ] W paczce są `admin/fonts/` (Open Sans 400, 600, 700, 800, latin i latin-ext, osiem plików woff2 z `@fontsource/open-sans`) oraz `admin/img/asapchat-logo-white.svg` (plik już jest w repo).
- [ ] Aktywacja wtyczki nie wysyła żadnego żądania HTTP (test z punktu 21.2).

### 02 Ekran wtyczki: rama, nagłówek i trzy stany

Etykiety: `feature`, `priority-high`, `risk-low`, `needs-qa`.

- [ ] Strona Ustawienia > AsapChat pokazuje stany „Niepołączony”, „Połączony” i „Błąd”: układ jak w makiecie, teksty i warianty według punktu 4, na danych przykładowych z kontraktu (punkty 4 i 7a).
- [ ] Nagłówek: czarny pasek, białe logo, fioletowa linia 4 px. W stanie „Połączony” nazwa sklepu, plan, „Otwórz panel AsapChat”, „Rozłącz”.
- [ ] Wszystkie klasy mają prefiks `asapchat-`, reguły są pod `.asapchat-screen`, CSS ładuje się tylko na stronie wtyczki.
- [ ] Ekran działa na 390 px i 1280 px. Karta Sieć nie pokazuje żądań do cudzych domen.
- [ ] Teksty są w kodzie po angielsku, dokładnie jak w punkcie 22.

### 03 Klient API i zapis danych

Etykiety: `feature`, `priority-high`, `risk-medium`, `skip-qa`.

- [ ] Klasa `Asapchat_Api` ma metody dla `POST /token`, `GET /config`, `PATCH /settings`, `DELETE /connection`. Używa `wp_remote_*`, timeout 5 s, nagłówki z punktu 10 (`POST /token` bez `Authorization` i bez `X-Site-Url`, `PATCH` jako JSON).
- [ ] Adres bazowy pochodzi ze stałej `ASAPCHAT_BASE`, którą da się nadpisać w `wp-config.php`.
- [ ] Opcje z punktu 5.1 mają poprawny `autoload`. Token nie trafia do `asapchat_config`.
- [ ] Testy PHPUnit na przykładowych odpowiedziach z kontraktu (punkty 4 i 7a, treści błędów z tabeli kodów, przykład `422`) pokrywają kody `200`, `400`, `401`, `409`, `410`, `422`, `429`, `500` i brak sieci.

### 04 Połącz przyciskiem

Etykiety: `feature`, `security`, `priority-high`, `risk-high`, `needs-qa`.

- [ ] Kliknięcie „Połącz z AsapChat” zapisuje `state` i `code_verifier` w transiencie na 2 godziny i przekierowuje na `/connect` z parametrami z punktu 6.
- [ ] Callback sprawdza uprawnienie i `state` przez `hash_equals`, kasuje transient, wymienia kod na token i zapisuje połączenie.
- [ ] „Anuluj”, zły `state`, wygasły transient i `invalid_grant` dają komunikaty z punktu 4.5. Nic nie jest zapisywane.
- [ ] Ścieżka nowego konta (punkt 6.1) kończy się w stanie „Połączony” bez ręcznych kroków.
- [ ] Wariant bez przycisku z punktu 4.2 (brak https, różne hosty albo porty): przycisk nieaktywny, zdanie o kluczu, pole klucza rozwinięte.

### 05 Klucz instalacyjny

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`.

- [ ] Zwinięte „Mam klucz instalacyjny” z polem i przyciskiem „Połącz kluczem” działa w stanach „Niepołączony” i „Błąd”.
- [ ] Poprawny klucz zapisuje połączenie i config tak samo jak przycisk.
- [ ] Zły format, `401` i `409` dają komunikaty z punktu 7. Klucz nie pojawia się w komunikacie ani w adresie.

### 06 Skrypt czatu i przełącznik „Czat”

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`.

- [ ] Tag czatu jest zbudowany z `chat.src` i każdej pary z `chat.attributes` według punktu 8.2.
- [ ] Tag pojawia się tylko, gdy spełnione są warunki z punktu 8.1 (w tym `src` spod `https://asapchat.io/`).
- [ ] Przełącznik „Czat” zapisuje się w `asapchat_settings` i po wyłączeniu tag znika.
- [ ] Obsługa strony sklepu nie wysyła żadnego żądania do AsapChat (test).

### 07 Wyszukiwarka i dodawanie do koszyka

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`.

- [ ] Tag wyszukiwarki jest wstawiany w `<head>`, gdy `search.enabled` to `true` i przełącznik jest włączony (test automatyczny na przykładzie z kontraktu, punkt 7a).
- [ ] Przy `search.enabled = false` przełącznik jest nieaktywny, ma wyjaśnienie i link na `sources.connect_url`. Zapisana wartość się nie zmienia.
- [ ] Wyłączone „Dodawanie do koszyka z wyszukiwarki” usuwa z tagu atrybut `allow_add-to-cart`.
- [ ] QA działającej wyszukiwarki: na sklepie pod publicznym adresem `https` (punkt 21.4). Na `localhost` sprawdzasz tylko stan nieaktywny.

### 08 Sekcja „Wygląd” i zapis przez PATCH /settings

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`.

- [ ] Karta ma pola: nazwa asystenta, kolor główny (pole koloru i pole tekstowe), pozycja, powitanie 1 i 2, z licznikami znaków. Wartości startowe z `settings` w configu.
- [ ] „Zapisz zmiany” wysyła `PATCH` tylko ze zmienionymi polami i zapisuje zwrócony config.
- [ ] Błędy lokalne i `422` są pokazane przy polach tekstami z punktu 4.3 (pole wskazują klucze `errors`). Wpisane wartości nie znikają.
- [ ] Przy `429`, `5xx` i braku sieci ustawienia lokalne są zapisane, a użytkownik widzi komunikat o niezapisanym wyglądzie.

### 09 Sekcja „Gdzie pokazywać”: opóźnienie i wykluczenia

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`.

- [ ] Karta ma pola: opóźnienie (0 do 60), strony, produkty (tylko z WooCommerce), „Adres zawiera”. Wartości są czyszczone według punktu 9.
- [ ] Na wykluczonej stronie, produkcie, stronie sklepu i adresie z pasującym fragmentem nie ma żadnego skryptu.
- [ ] Przy opóźnieniu większym od 0 czat dokłada lokalny plik `public/asapchat-delay.js`, z tymi samymi atrybutami co zwykły tag.
- [ ] Bez WooCommerce ekran i front działają bez błędów PHP.

### 10 Sekcje „Status” i „Źródło zamówień i produktów”

Etykiety: `feature`, `priority-medium`, `risk-low`, `needs-qa`.

- [ ] Status czatu i wyszukiwarki pokazuje pigułki i opisy z tabeli w punkcie 4.3, daty w strefie czasowej witryny.
- [ ] Źródła pokazują dwa statusy z `sources` i przycisk „Podłącz w AsapChat” na `sources.connect_url` (nowa karta).
- [ ] Rozwijana instrukcja kluczy WooCommerce ma pięć kroków i link do ekranu kluczy REST API.
- [ ] Bez WooCommerce instrukcja jest ukryta i widać zdanie o bazie wiedzy.
- [ ] QA stanu „Podłączone” i daty wyszukiwarki: na sklepie pod publicznym adresem `https` (punkt 21.4).

### 11 Odświeżanie configu, błędy i rozłączenie

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`.

- [ ] Config odświeża się przy otwarciu ekranu wtyczki i co `refresh_after` sekund przez WP-Cron.
- [ ] `401`, `409`, `410` kasują token i config, zdejmują skrypty i pokazują stan „Błąd” z właściwym opisem.
- [ ] `429`, `5xx` i brak sieci niczego nie kasują. Skrypty działają dalej.
- [ ] „Rozłącz” woła `DELETE /connection`, kasuje połączenie i config, zostawia `asapchat_settings`.

### 12 Usunięcie wtyczki, multisite, HPOS

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`.

- [ ] `uninstall.php` próbuje `DELETE /connection`, potem usuwa wszystkie opcje, transienty i zdarzenie crona, także dla każdej witryny w multisite.
- [ ] Dezaktywacja usuwa zdarzenie crona i zostawia dane.
- [ ] Dwie witryny w multisite mają niezależne połączenia (dwa konta AsapChat, punkt 17).
- [ ] WooCommerce nie pokazuje ostrzeżenia o niezgodności z HPOS (deklaracja jest w `asapchat.php` od zadania 01, tutaj ją sprawdzasz z włączonym HPOS).

### 13 Tłumaczenia, readme.txt, grafiki i macierz testów

Etykiety: `documentation`, `priority-medium`, `risk-low`, `needs-qa`.

- [ ] `languages/asapchat.pot` i komplet `pl_PL` pokrywają wszystkie teksty ekranu, zgodnie z tabelą w punkcie 22.
- [ ] `readme.txt` według `docs/marketplace-listing.md` przechodzi walidator i ma sekcję „External services” z adresami potwierdzonymi w karcie Sieć.
- [ ] Grafiki z `docs/marketplace-listing.md`, punkt 9, leżą w `.wordpress-org/`.
- [ ] Wszystkie scenariusze z punktu 17 mają wpisany wynik i zrzuty w issue tego zadania.

### 14 Wydanie 1.0.0 i zgłoszenie do WordPress.org

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`.

- [ ] Workflow `release` buduje zip z tagu `v*`, bez plików deweloperskich, poniżej 1 MB.
- [ ] Zip zainstalowany na czystym WordPressie przechodzi Plugin Check bez błędów.
- [ ] Checklista z `docs/marketplace-listing.md`, punkt 10, jest odhaczona w PR.
- [ ] Wtyczka zgłoszona z konta wydawcy z prośbą o slug `asapchat`.

## 21. Narzędzia, testy i środowisko lokalne

### 21.1 Co instalujesz

- Docker (dla `wp-env`), Node 20 LTS, Composer 2, PHP 8.1 lub nowszy lokalnie.
- npm służy tylko narzędziom deweloperskim (`wp-env`, pobranie fontów). Do paczki wtyczki nie trafia nic z `node_modules` ani z `vendor`.

`composer.json`, sekcja `require-dev`: `phpunit/phpunit` (^9.6), `brain/monkey` (^2.6), `yoast/phpunit-polyfills`, `wp-coding-standards/wpcs` (^3), `phpcompatibility/phpcompatibility-wp`, `dealerdirect/phpcodesniffer-composer-installer`. W `config.platform.php` wpisz `7.4`, żeby zależności instalowały się na każdej wersji z macierzy.

`phpcs.xml.dist`: standardy `WordPress` i `PHPCompatibilityWP`, `testVersion` = `7.4-`, text domain `asapchat`, prefiksy `asapchat` i `Asapchat`, bez katalogów `vendor`, `node_modules`, `tests`, `docs`, `.ai`.

### 21.2 Testy automatyczne

- PHPUnit z Brain Monkey: testy jednostkowe bez uruchamiania WordPressa. Funkcje WordPressa (`get_option`, `wp_remote_request`, `home_url` itd.) podstawiasz przez `Brain\Monkey\Functions`.
- Odpowiedzi AsapChat to stałe tablice zbudowane z przykładów w `docs/CONNECT-API.md` (punkty 4 i 7a, tabela kodów błędów, przykład `422`). Trzymasz je w `tests/fixtures/`.
- Test „zero żądań przed zgodą”: załadowanie pliku głównego i wywołanie aktywacji przy `Functions\expect( 'wp_remote_request' )->never()` (to samo dla `wp_remote_get` i `wp_remote_post`).
- Test „front nie woła AsapChat”: to samo oczekiwanie przy budowaniu tagów w `Asapchat_Frontend`.
- Test nagłówków: każde wywołanie z tokenem ma `Authorization`, `Accept` i `X-Site-Url`; `POST /token` nie ma `Authorization`.

### 21.3 CI i zasoby

`.github/workflows/ci.yml`, uruchamiany dla każdego PR:

1. Zadanie `php` w macierzy PHP 7.4, 8.1, 8.3: `composer install`, `vendor/bin/phpcs`, `vendor/bin/phpunit`.
2. Zadanie `plugin-check` (Node 20): `npx wp-env start`, potem `npx wp-env run cli wp plugin check asapchat`.

Bramka walidacji lokalnie (te same komendy są w `AGENTS.md` i `.ai/agentic.config.json`):

```
composer install
vendor/bin/phpcs
vendor/bin/phpunit
npx wp-env start
npx wp-env run cli wp plugin check asapchat
```

Fonty: `npm pack @fontsource/open-sans`, z rozpakowanego archiwum bierzesz z katalogu `files/` osiem plików `open-sans-latin-{400,600,700,800}-normal.woff2` i `open-sans-latin-ext-{400,600,700,800}-normal.woff2` i kopiujesz je do `admin/fonts/`. Licencję (SIL OFL 1.1) wpisujesz w `readme.txt`.

### 21.4 Lokalny sklep krok po kroku

`.wp-env.json` w katalogu głównym repo:

```json
{
  "core": null,
  "phpVersion": "8.3",
  "port": 8888,
  "plugins": [
    "https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip",
    "https://downloads.wordpress.org/plugin/plugin-check.latest-stable.zip"
  ],
  "mappings": { "wp-content/plugins/asapchat": "." },
  "config": { "WP_DEBUG": true, "WP_DEBUG_LOG": true }
}
```

Mapowanie jest potrzebne, bo katalog repo nazywa się `woocommerce-asapchat`, a wtyczka musi leżeć w `plugins/asapchat`.

1. `npx wp-env start`. Sklep: `http://localhost:8888`, panel: `http://localhost:8888/wp-admin` (login `admin`, hasło `password`).
2. `npx wp-env run cli wp plugin activate asapchat`.
3. Dane przykładowe WooCommerce: `npx wp-env run cli wp plugin install wordpress-importer --activate`, potem `npx wp-env run cli wp import wp-content/plugins/woocommerce/sample-data/sample_products.xml --authors=create`.
4. Motyw sklepu: `npx wp-env run cli wp theme install storefront --activate`.
5. Załóż własne darmowe konto AsapChat (`docs/CONNECT-API.md`, punkt 7) i kliknij „Połącz z AsapChat” na ekranie wtyczki. Adres `http://localhost:8888` przechodzi bez `https`.
6. Inna wersja PHP albo WordPressa z macierzy: zmień `phpVersion` lub `core` w pliku `.wp-env.override.json` (nie commituj go) i uruchom `npx wp-env start --update`.

Na `localhost` sprawdzisz połączenie, czat, wygląd, wykluczenia, opóźnienie, błędy i rozłączenie. Wyszukiwarkę i stan „Podłączone” sprawdzisz dopiero na sklepie widocznym z internetu pod `https`: uruchom tunel do portu 8888 (np. `cloudflared tunnel --url http://localhost:8888`), ustaw adres tunelu jako adres witryny (`npx wp-env run cli wp option update home <adres>` i to samo dla `siteurl`), połącz wtyczkę od nowa i podłącz WooCommerce w panelu AsapChat (`sources.connect_url`). Szczegóły ograniczeń: kontrakt, punkt 7.

## 22. Teksty UI: angielski (źródło) i polski (tłumaczenie)

W kodzie wpisujesz tekst angielski. Polski trafia do `languages/asapchat-pl_PL.po`. `%s` i `%d` to miejsca na wartości. Nazwy „AsapChat”, „AsapSearch”, „WooCommerce”, „Consumer key” i „Consumer secret” nie są tłumaczone.

### Ogólne i stan „Niepołączony”

| EN (w kodzie) | PL |
|---|---|
| AsapChat | AsapChat |
| Connect your store to AsapChat | Połącz sklep z AsapChat |
| AI chat and product search on your site, with no code to paste. | Czat AI i wyszukiwarka produktów na Twojej stronie, bez wklejania kodu. |
| Connect to AsapChat | Połącz z AsapChat |
| No account yet? You will create one in the next step, for free. | Nie masz konta? Założysz je w następnym kroku, za darmo. |
| Click "Connect to AsapChat". | Klikasz „Połącz z AsapChat”. |
| Log in or create an account and go through a short setup wizard. | Logujesz się albo zakładasz konto i przechodzisz krótki kreator. |
| You come back here automatically. The chat is already on your site. | Wracasz tutaj automatycznie. Czat jest już na stronie. |
| After you click, you will go to panel.asapchat.io. The plugin connects to AsapChat only from that moment. | Po kliknięciu przejdziesz na panel.asapchat.io. Wtyczka łączy się z AsapChat dopiero od tego momentu. |
| Privacy policy | Polityka prywatności |
| Terms of service | Regulamin |
| I have an install key | Mam klucz instalacyjny |
| Install key | Klucz instalacyjny |
| Connect with key | Połącz kluczem |
| You will find the key in the AsapChat panel: Shop, Plugin, Install key. | Klucz znajdziesz w panelu AsapChat: Sklep, Wtyczka, Klucz instalacyjny. |
| Connecting with the button requires an https address and an admin panel on the same domain as the site. Use an install key. | Połączenie przyciskiem wymaga adresu https i panelu na tej samej domenie co strona. Użyj klucza instalacyjnego. |

### Stan „Połączony”: nagłówek, status, widgety

| EN (w kodzie) | PL |
|---|---|
| Open AsapChat panel | Otwórz panel AsapChat |
| Disconnect | Rozłącz |
| Disconnect this site from AsapChat? The chat and the search will disappear from your site. | Rozłączyć tę stronę z AsapChat? Czat i wyszukiwarka znikną ze strony. |
| %1$s plan, %2$d AI conversations per month | Plan %1$s, %2$d rozmów AI miesięcznie |
| %s plan | Plan %s |
| Status | Status |
| We check whether the widgets actually appeared on your site. | Sprawdzamy, czy widgety faktycznie pojawiły się na Twojej stronie. |
| Chat | Czat |
| Chat is working | Czat działa |
| Last seen %s | Ostatnio widziany %s |
| Not seen on the site yet | Jeszcze niewidziany na stronie |
| Open your store in a new tab to check. | Otwórz sklep w nowej karcie, żeby sprawdzić. |
| Search | Wyszukiwarka |
| Search is working | Wyszukiwarka działa |
| Last seen %s (kontekst: search) | Ostatnio widziana %s |
| Not seen on the site yet (kontekst: search) | Jeszcze niewidziana na stronie |
| Unavailable | Niedostępna |
| Connect your products first. | Najpierw podłącz produkty. |
| Widgets | Widgety |
| Turn on or off what should be visible on your site. | Włącz albo wyłącz to, co ma być widoczne na stronie. |
| A conversation window with the AI assistant on your site. | Okno rozmowy z asystentem AI na stronie. |
| AsapSearch product search | Wyszukiwarka AsapSearch |
| Replaces the store search with a fast product search. | Zastępuje wyszukiwarkę sklepu szybką wyszukiwarką produktów. |
| The search will turn on once AsapChat imports your products. | Wyszukiwarka włączy się, gdy AsapChat zaimportuje Twoje produkty. |
| Connect products | Podłącz produkty |
| Add to cart from search | Dodawanie do koszyka z wyszukiwarki |
| An "Add to cart" button right in the search results. | Przycisk „Do koszyka” od razu w wynikach wyszukiwania. |
| Available together with the search. | Dostępne razem z wyszukiwarką. |

Teksty z dopiskiem „kontekst” wpisujesz przez `_x()` z kontekstem `search`, bo po polsku mają inny rodzaj gramatyczny niż wersja dla czatu.

### Stan „Połączony”: wygląd, gdzie pokazywać, źródła, zapis

| EN (w kodzie) | PL |
|---|---|
| Look | Wygląd |
| These are the same settings as in the AsapChat panel. A change here is visible there and the other way round. | To te same ustawienia co w panelu AsapChat. Zmiana tutaj jest widoczna tam i odwrotnie. |
| Assistant name | Nazwa asystenta |
| Main color | Kolor główny |
| Position on the page | Pozycja na stronie |
| Bottom right | Prawy dół |
| Bottom left | Lewy dół |
| Greeting 1 | Powitanie 1 |
| Greeting 2 | Powitanie 2 |
| The assistant name can have up to 40 characters. | Nazwa asystenta może mieć do 40 znaków. |
| Enter the assistant name. | Podaj nazwę asystenta. |
| Enter the color as #rrggbb, e.g. #6543f0. | Podaj kolor w formacie #rrggbb, np. #6543f0. |
| A greeting can have up to 120 characters. | Powitanie może mieć do 120 znaków. |
| Choose a position from the list. | Wybierz pozycję z listy. |
| Where to show | Gdzie pokazywać |
| On the selected pages and products we will show neither the chat nor the search. | Na wybranych stronach i produktach nie pokażemy ani czatu, ani wyszukiwarki. |
| Chat delay | Opóźnienie czatu |
| seconds | sekund |
| How many seconds after entering the page the chat appears. From 0 to 60. | Po ilu sekundach od wejścia na stronę pojawia się czat. Od 0 do 60. |
| Pages without widgets | Strony bez widgetów |
| Products without widgets | Produkty bez widgetów |
| Address contains | Adres zawiera |
| One address fragment per line. The widgets will not appear on any page whose address contains this text. | Jeden fragment adresu w linii. Widgetów nie będzie na każdej stronie, której adres zawiera ten tekst. |
| Orders and products source | Źródło zamówień i produktów |
| Thanks to this the assistant knows your products and answers questions about order status. | Dzięki temu asystent zna Twoje produkty i odpowiada na pytania o status zamówienia. |
| Products | Produkty |
| Orders | Zamówienia |
| Connected | Podłączone |
| Not connected | Niepodłączone |
| Connect in AsapChat | Podłącz w AsapChat |
| In the AsapChat panel click "Connect with WooCommerce". You will see the WooCommerce consent screen, you approve it and you are done. You do not copy any keys. | W panelu AsapChat kliknij „Połącz z WooCommerce”. Zobaczysz ekran zgody WooCommerce, zatwierdzasz i gotowe. Nie kopiujesz żadnych kluczy. |
| How to generate API keys in WooCommerce | Jak wygenerować klucze API w WooCommerce |
| Go to WooCommerce, Settings, Advanced, REST API. | Wejdź w WooCommerce, Ustawienia, Zaawansowane, REST API. |
| Click "Add key". | Kliknij „Dodaj klucz”. |
| Enter a description, e.g. "AsapChat". Permissions: "Read". | Wpisz opis, np. „AsapChat”. Uprawnienia: „Odczyt”. |
| Click "Generate API key". | Kliknij „Wygeneruj klucz API”. |
| Copy the Consumer key and the Consumer secret. Paste them in the AsapChat panel after clicking "Connect in AsapChat". The secret is visible only once. | Skopiuj Consumer key i Consumer secret. Wklej je w panelu AsapChat po kliknięciu „Podłącz w AsapChat”. Secret jest widoczny tylko raz. |
| No WooCommerce? The assistant then uses the knowledge base that you fill in the AsapChat panel. | Nie masz WooCommerce? Asystent korzysta wtedy z bazy wiedzy, którą uzupełnisz w panelu AsapChat. |
| Save changes | Zapisz zmiany |
| The look is saved on your AsapChat account. The other settings stay in WordPress. | Wygląd zapisujemy na Twoim koncie AsapChat. Pozostałe ustawienia zostają w WordPressie. |

### Stan „Błąd” i komunikaty

| EN (w kodzie) | PL |
|---|---|
| The chat and the search are not being added to your site right now. | Czat i wyszukiwarka nie są teraz dodawane do strony. |
| The connection with AsapChat was interrupted | Połączenie z AsapChat zostało przerwane |
| The connection was revoked in the AsapChat panel or the install key expired. Connect the site again to bring the chat back. | Połączenie odwołano w panelu AsapChat albo klucz instalacyjny wygasł. Połącz stronę ponownie, żeby przywrócić czat. |
| This connection is assigned to another domain. Connect this site again. | To połączenie jest przypisane do innej domeny. Połącz tę stronę ponownie. |
| This shop was deleted in AsapChat. Connect the site to another shop. | Ten sklep został usunięty w AsapChat. Połącz stronę z innym sklepem. |
| Connect again | Połącz ponownie |
| Your widget and exclusion settings have been kept. | Twoje ustawienia widgetów i wykluczeń zostały zachowane. |
| Connected to the shop %s. The chat is already on your site. | Połączono ze sklepem %s. Czat jest już na Twojej stronie. |
| Connection cancelled. Nothing has been changed. | Połączenie anulowane. Nic nie zostało zmienione. |
| The connection session expired. Click Connect again. | Sesja łączenia wygasła. Kliknij Połącz ponownie. |
| Could not connect. Try again or use an install key. | Nie udało się połączyć. Spróbuj ponownie albo użyj klucza instalacyjnego. |
| Changes saved. | Zapisano zmiany. |
| Settings in WordPress saved. The look could not be saved in AsapChat, try again in a moment. | Ustawienia w WordPressie zapisane. Wyglądu nie udało się zapisać w AsapChat, spróbuj ponownie za chwilę. |
| Could not refresh the settings from AsapChat. The chat keeps working on the saved settings. | Nie udało się odświeżyć ustawień z AsapChat. Czat działa dalej na zapisanych ustawieniach. |
| Disconnected. The chat and the search have disappeared from your site. | Rozłączono. Czat i wyszukiwarka zniknęły ze strony. |
| Update the AsapChat plugin to get the new settings. | Zaktualizuj wtyczkę AsapChat, żeby pobrać nowe ustawienia. |
| This does not look like an install key. | To nie wygląda na klucz instalacyjny. |
| The key is invalid, revoked or expired. The key must be used within 24 hours of being generated. | Klucz jest nieprawidłowy, odwołany albo wygasł. Klucz trzeba użyć w ciągu 24 godzin od wygenerowania. |
| This key is already assigned to another domain. | Ten klucz jest już przypisany do innej domeny. |
| Could not connect. Try again in a moment. | Nie udało się połączyć. Spróbuj ponownie za chwilę. |
| Dismiss this notice | Zamknij ten komunikat |
