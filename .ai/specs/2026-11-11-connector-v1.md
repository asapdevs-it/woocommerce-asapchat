# Specyfikacja: wtyczka WordPress `asapchat`, wersja 1

Data: 2026-11-11. Status: zatwierdzona do realizacji.

Kontrakt z AsapChat jest gotowy i opisany w `docs/CONNECT-API.md`. Przeczytaj go w całości przed pierwszym issue. Gdy ten dokument i kontrakt mówią co innego o polach lub kodach odpowiedzi, wygrywa kontrakt. Zgłoś wtedy różnicę w issue.

## 1. Cel

Właściciel strony na WordPressie instaluje wtyczkę z katalogu WordPress.org, klika jeden przycisk i ma na stronie czat AsapChat oraz wyszukiwarkę AsapSearch (gdy sklep ma zaimportowane produkty). Bez wklejania kodu i bez edycji motywu. Na ekranie wtyczki ustawia też wygląd czatu, miejsca, w których widgety mają się nie pokazywać, i widzi, czy asystent ma dostęp do produktów i zamówień.

Miara sukcesu: od aktywacji wtyczki do widocznego czatu mija mniej niż 2 minuty u osoby, która ma już konto AsapChat.

## 2. Słownik

- **AsapChat**: usługa pod adresem `https://panel.asapchat.io`. Adres trzymasz w jednej stałej `ASAPCHAT_BASE`.
- **Token połączenia**: tajny ciąg `ack_...` (52 znaki). Wtyczka przedstawia się nim usłudze. Dostaje go raz, przy połączeniu.
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

Jedna strona: **Ustawienia > AsapChat** (`options-general.php?page=asapchat`). Uprawnienie: `manage_options`. Makieta wszystkich stanów: `docs/mockups/settings.html` (otwórz w przeglądarce, przełącz stan u góry). Teksty w makiecie są wzorcem polskiego tłumaczenia.

### 4.1 Wygląd wspólny

Ekran wygląda jak panel AsapChat osadzony w panelu WordPressa. Menu boczne i górna belka WordPressa zostają bez zmian.

- **Nagłówek ekranu**: czarny pasek (`#161616`) z białym logo AsapChat (`asapchat-logo-white.svg`, plik w paczce wtyczki), pod nim fioletowa linia 4 px (`#6543f0`).
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
3. Font leży w paczce: `admin/fonts/*.woff2` (podzbiory latin i latin-ext, żeby działały polskie znaki). Ładujesz go przez `@font-face` w `admin/admin.css`. Żadnych odwołań do Google Fonts ani innych zewnętrznych adresów w panelu admina.
4. Rozwijane bloki to natywny `<details>`. Kolor to natywny `<input type="color">` obok pola tekstowego z wartością `#rrggbb`. Jedyny własny JS w panelu to kilkanaście linii w `admin/admin.js`, które synchronizują te dwa pola.
5. Ekran działa na szerokości 390 px (telefon) i 1280 px.

### 4.2 Stan „Niepołączony”

Kolejność elementów:

1. Nagłówek z logo.
2. Tytuł „Połącz sklep z AsapChat” i jedno zdanie: „Czat AI i wyszukiwarka produktów na Twojej stronie, bez wklejania kodu.”
3. Przycisk główny „Połącz z AsapChat”.
4. Pod przyciskiem: „Nie masz konta? Założysz je w następnym kroku, za darmo.”
5. Informacja o zgodzie: „Po kliknięciu przejdziesz na panel.asapchat.io. Wtyczka łączy się z AsapChat dopiero od tego momentu.” oraz linki „Polityka prywatności” i „Regulamin”.
6. Zwinięte „Mam klucz instalacyjny”: pole tekstowe i biały przycisk „Połącz kluczem”. Podpowiedź: „Klucz znajdziesz w panelu AsapChat: Sklep, Wtyczka, Klucz instalacyjny.”

Gdy `home_url()` nie zaczyna się od `https://` i host nie jest `localhost` ani `127.0.0.1`, albo gdy `home_url()` i `admin_url()` mają różne hosty, przycisk „Połącz z AsapChat” jest nieaktywny. Pokazujesz wtedy zdanie: „Połączenie przyciskiem wymaga adresu https. Użyj klucza instalacyjnego.” i rozwijasz pole klucza.

### 4.3 Stan „Połączony”

Sekcje w tej kolejności. Każda sekcja od 2 do 6 to osobna karta.

**1. Nagłówek.** W czarnym pasku obok logo: link „Otwórz panel AsapChat” (`links.panel`, nowa karta) i „Rozłącz”. Pod paskiem: nazwa sklepu (`shop.name`) jako tytuł, pigułka z planem i adres strony. Plan: gdy `shop.plan` to `free`, tekst „Plan Free, {ai_conversations_limit} rozmów AI miesięcznie”. Dla innych planów sama nazwa planu. „Rozłącz” wymaga potwierdzenia (`confirm` w przeglądarce wystarczy).

**2. Status.** Dwa wiersze:

| Wiersz | Warunek | Pigułka | Opis |
|---|---|---|---|
| Czat | `status.widget_seen_at` ma datę | zielona „Czat działa” | „Ostatnio widziany {data}” |
| Czat | `status.widget_seen_at` to `null` | szara „Jeszcze nie widziany” | „Otwórz sklep w nowej karcie, żeby sprawdzić.” z linkiem do `home_url()` |
| Wyszukiwarka | `status.search_seen_at` ma datę | zielona „Wyszukiwarka działa” | „Ostatnio widziana {data}” |
| Wyszukiwarka | `search.enabled` to `true`, brak daty | szara „Jeszcze nie widziana” | jak przy czacie |
| Wyszukiwarka | `search.enabled` to `false` | szara „Niedostępna” | „Najpierw podłącz produkty.” |

Daty pokazujesz w strefie czasowej witryny (`wp_date`).

**3. Widgety.** Trzy przełączniki. To ustawienia lokalne.

| Przełącznik | Domyślnie | Uwagi |
|---|---|---|
| Czat | włączony | wyłączony = skrypt czatu nie trafia na stronę |
| Wyszukiwarka AsapSearch | włączony | gdy `search.enabled` to `false`: przełącznik nieaktywny, pod nim „Wyszukiwarka włączy się, gdy AsapChat zaimportuje Twoje produkty.” i link „Podłącz produkty” na `sources.connect_url` |
| Dodawanie do koszyka z wyszukiwarki | włączony | nieaktywny, gdy wyszukiwarka jest wyłączona lub niedostępna |

Nieaktywny przełącznik nie zmienia zapisanej wartości. Dzięki temu wyszukiwarka pojawi się sama po imporcie produktów.

**4. Wygląd.** Pola zapisywane w AsapChat przez `PATCH /api/plugin/v1/settings`. Wartości startowe bierzesz z `settings` w configu.

| Pole | Klucz | Reguła |
|---|---|---|
| Nazwa asystenta | `bot_name` | tekst, do 40 znaków |
| Kolor główny | `main_color` | `#rrggbb`, pole koloru i pole tekstowe |
| Pozycja | `position` | „Prawy dół” = `right-bottom`, „Lewy dół” = `left-bottom` |
| Powitanie 1 | `greeting_1` | tekst do 120 znaków, puste = bez dymka |
| Powitanie 2 | `greeting_2` | tekst do 120 znaków, puste = bez dymka |

Pod tytułem karty zdanie: „To te same ustawienia co w panelu AsapChat. Zmiana tutaj jest widoczna tam i odwrotnie.”

**5. Gdzie pokazywać.** Ustawienia lokalne.

| Pole | Reguła |
|---|---|
| Opóźnienie czatu | liczba sekund od 0 do 60, domyślnie 0. Opis: „Po ilu sekundach od wejścia na stronę pojawia się czat.” |
| Strony bez widgetów | wybór wielu stron z listy stron WordPressa |
| Produkty bez widgetów | wyszukiwarka produktów WooCommerce, wybór wielu. Pole widoczne tylko z aktywnym WooCommerce |
| Adres zawiera | pole wieloliniowe, jeden fragment adresu w linii, np. `/koszyk` albo `/kategoria-produktu/doniczki`. Do 20 linii, każda do 100 znaków |

Opis pod tytułem karty: „Na wybranych stronach i produktach nie pokażemy ani czatu, ani wyszukiwarki.”

Jak zbudować wybór: strony pobierasz przez `get_pages()` i pokazujesz jako `<select multiple>`. Produkty: `<select class="wc-product-search" multiple data-action="woocommerce_json_search_products">` i dołączony skrypt WooCommerce `wc-enhanced-select`. Nie pisz własnej wyszukiwarki produktów. Całą kategorię produktów wyklucza się fragmentem adresu.

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

Wtyczka sama nie tworzy kluczy API i nie wysyła ich do AsapChat. Pole `sources.systems` z configu w wersji 1 pomijasz.

**Zapis.** Pod kartami jeden główny przycisk „Zapisz zmiany”. Zapisuje sekcje 3, 4 i 5 jednym formularzem (opis w punkcie 9).

**Brak paska aktualizacji.** Wtyczkę aktualizuje WordPress.org. Pole `plugin.latest_version` z configu pomijasz. Wtyczka nie ma własnego mechanizmu aktualizacji ani komunikatu o nowej wersji.

### 4.4 Stan „Błąd, połącz ponownie”

Pokazywany, gdy AsapChat odpowiedział `401`, `409` albo `410` (punkt 10). Skrypty nie są wtedy wstawiane.

| Kod | Tytuł | Opis |
|---|---|---|
| `401` | „Połączenie z AsapChat zostało przerwane” | „Połączenie odwołano w panelu AsapChat. Połącz stronę ponownie, żeby przywrócić czat.” |
| `409` | jak wyżej | „To połączenie jest przypisane do innej domeny. Połącz tę stronę ponownie.” |
| `410` | jak wyżej | „Ten sklep został usunięty w AsapChat. Połącz stronę z innym sklepem.” |

Przycisk główny „Połącz ponownie” uruchamia ten sam przepływ co „Połącz”. Pod nim zwinięte „Mam klucz instalacyjny”. Zdanie na dole: „Twoje ustawienia widgetów i wykluczeń zostały zachowane.”

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
2. Handler `admin_post_asapchat_connect_key`: nonce, uprawnienie, `sanitize_text_field`. Klucz musi zaczynać się od `ack_` i mieć 52 znaki. Inaczej: „To nie wygląda na klucz instalacyjny.”
3. `GET /api/plugin/v1/config` z `Authorization: Bearer {klucz}` i `X-Site-Url: {home_url()}`.
4. `200`: zapis jak w kroku 7.6 przepływu „Połącz”. Nazwę i ID sklepu bierzesz z `shop` w configu.
5. `401`: „Klucz jest nieprawidłowy albo odwołany.” `409`: „Ten klucz jest już przypisany do innej domeny.” Inne błędy: „Nie udało się połączyć. Spróbuj ponownie za chwilę.”

## 8. Wstawianie skryptów na stronie

Robi to klasa `Asapchat_Frontend` na `wp_enqueue_scripts`. Front czyta tylko zapisane opcje. Nigdy nie wysyła żądania do AsapChat.

### 8.1 Kiedy wstawiamy

Wszystkie warunki muszą być spełnione:

- `asapchat_connection.status` to `ok`,
- blok w `asapchat_config` (`chat` albo `search`) ma `enabled = true` i `src` zaczyna się od `https://` albo od adresu ze stałej `ASAPCHAT_BASE`,
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
4. Wysyłasz `PATCH /api/plugin/v1/settings` tylko ze zmienionymi polami.
5. `200`: odpowiedź to pełny config. Zapisujesz go od razu w `asapchat_config`. Komunikat „Zapisano zmiany.”
6. `422`: pokazujesz błędy z pola `errors` przy właściwych polach. Wpisane wartości zostają w formularzu (transient `asapchat_form_{user_id}`).
7. `401`, `409`, `410`: jak w punkcie 10, przechodzisz do stanu „Błąd”.
8. `429`, `5xx`, brak sieci: komunikat o niezapisanym wyglądzie, wpisane wartości zostają w formularzu. Ustawienia lokalne są już zapisane.

## 10. Odświeżanie configu i błędy

Kiedy pobierasz `GET /config`:

- Przy każdym otwarciu ekranu wtyczki w stanie „Połączony”.
- Co `refresh_after` sekund (pole z configu, zwykle 86400) przez WP-Cron. Zdarzenie jednorazowe `asapchat_refresh_config`, planowane od nowa po każdym pobraniu. Usuwane przy rozłączeniu i dezaktywacji.
- **Nigdy** podczas żądania strony sklepu.

Każde wywołanie ma nagłówki `Authorization: Bearer {token}`, `Accept: application/json`, `X-Site-Url: {home_url()}`. Timeout 5 sekund.

| Odpowiedź | Co robi wtyczka |
|---|---|
| `200` | Nadpisuje `asapchat_config`, ustawia `fetched_at`, planuje następne odświeżenie |
| `401`, `410` | Kasuje token i `asapchat_config`, usuwa zdarzenie crona, zapisuje `status = error` i `error_code`. Skrypty znikają ze strony. Ekran pokazuje stan 4.4 |
| `409` | Jak wyżej, z opisem o innej domenie |
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

Te zasady obowiązują w każdym issue. Pełna checklista zgłoszenia: `docs/marketplace-listing.md`.

1. **Zero żądań do zewnętrznych adresów przed zgodą.** Świeżo aktywowana wtyczka nie wysyła niczego. Pierwsze żądanie pada po kliknięciu „Połącz” albo po wysłaniu klucza.
2. **Sekcja „External services” w `readme.txt`.** Opisuje, co, kiedy i dokąd wysyła wtyczka, z linkami do regulaminu i polityki prywatności.
3. **Żadnych zdalnych fontów ani zasobów w panelu admina.** Open Sans i logo leżą w paczce. Jedyne zdalne zasoby to skrypty widgetów na froncie, spod adresów z configu.
4. **GPL.** Kod i grafiki na licencji GPLv2 lub późniejszej. Open Sans ma licencję zgodną z GPL. Dopisz ją w `readme.txt`.
5. **Plugin Check** bez błędów w kategorii „Plugin Repo”.
6. Brak własnego mechanizmu aktualizacji. Brak iframe w panelu. Kod czytelny, bez minifikacji i zaciemniania.
7. Komunikaty tylko na stronie wtyczki, zamykane.

Ryzyko: zespół WordPress.org może poprosić o poprawki albo odrzucić zgłoszenie (np. z powodu oznaczenia marki w widgecie). Wtedy poprawiamy i wgrywamy ponownie. To nie wstrzymuje prac nad kodem.

## 15. Tłumaczenia

Teksty źródłowe po angielsku, przez `__()` / `esc_html__()` z domeną `asapchat`. W repo `languages/asapchat.pot` i komplet `pl_PL`. Polskie teksty wzorcowe są w makiecie i w tym dokumencie. Forma „Ty”, krótko, bez pauz (długi myślnik, U+2014).

## 16. Bezpieczeństwo (checklista do review)

- [ ] Brak żądań do AsapChat przed kliknięciem „Połącz” lub wysłaniem klucza (test automatyczny z filtrem `pre_http_request`).
- [ ] Nonce i `manage_options` na każdej akcji `admin-post` poza callbackiem. Callback: `manage_options` i `state` przez `hash_equals`.
- [ ] `state` i `code_verifier` jednorazowe. Transient kasowany przed wymianą kodu.
- [ ] Adres AsapChat w jednej stałej `ASAPCHAT_BASE`. Nadpisanie tylko przez `define` w `wp-config.php`.
- [ ] Token nie pojawia się w HTML, w komunikatach, w logach, w adresie URL ani w JS.
- [ ] Wszystkie wartości z configu escapowane przy wyświetlaniu. `src` bez `https://` nie jest renderowany.
- [ ] Wszystkie pola formularza czyszczone przed zapisem (punkt 9).
- [ ] Brak endpointów REST, akcji AJAX `nopriv` i parametrów diagnostycznych.
- [ ] `uninstall.php` sprawdza `WP_UNINSTALL_PLUGIN`.
- [ ] CSS i JS panelu ładowane tylko na stronie wtyczki.

## 17. Macierz testów

| Oś | Warianty |
|---|---|
| WordPress | najnowszy, najnowszy minus 2 wersje główne |
| PHP | 7.4, 8.1, 8.4 |
| WooCommerce | brak; najnowszy z włączonym HPOS |
| Motyw | Storefront, motyw blokowy (Twenty Twenty-Five), Astra |
| Cache | WP Rocket, LiteSpeed Cache, Autoptimize (każdy z opóźnianiem i łączeniem JS) |
| Zgody | jedna wtyczka cookies (np. Complianz) aktywna |
| Sieć | pojedyncza witryna, multisite (2 witryny, różne sklepy) |
| Szerokość ekranu wtyczki | 390 px, 1280 px |

Wszystkie scenariusze przechodzisz na kombinacji podstawowej (najnowszy WP, PHP 8.4, WooCommerce, Storefront). Na pozostałych wyrywkowo: 1, 2, 6, 10, 16.

Konto testowe i lokalny sklep: `docs/CONNECT-API.md`, punkt 7.

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

- Wszystkie issues z punktu 20 zamknięte. CI zielone: PHPCS, PHPUnit, Plugin Check bez błędów.
- Wszystkie sekcje ekranu z punktu 4 działają i wyglądają jak w makiecie, na 390 px i 1280 px.
- Macierz testów przejścia udokumentowana (tabela i zrzuty) w issue 13.
- `readme.txt` przechodzi walidator WordPress.org i zawiera sekcję „External services”.
- Panel admina nie pobiera niczego z zewnętrznych adresów.
- Paczka zip poniżej 1 MB (z fontami), bez plików deweloperskich (`.git`, `tests`, `vendor`, `.ai`, `docs`).
- Przypadki z `docs/CONNECT-API.md`, punkt 7.5, przeklikane przed wydaniem.
- Opiekun repozytorium zatwierdził PR wydania.

## 19. Ustalenia

1. **Różne hosty `home_url()` i `admin_url()`** albo strona bez https: tylko klucz instalacyjny (punkt 4.2).
2. **Slug.** W formularzu zgłoszenia wpisujesz prośbę o slug `asapchat`. Zapasowy: `asapchat-ai-chat`.
3. **„Tested up to”.** Wpisujesz wersję aktualną w dniu wydania.
4. **Wtyczki cache.** `data-no-optimize` działa w LiteSpeed i WP Rocket. Dla Autoptimize dodajesz filtr wykluczenia, jeśli scenariusz 19 tego wymaga.
5. **Kategorie produktów** nie mają osobnego pola wykluczeń. Wyklucza się je fragmentem adresu.

## 20. Issues

Kolejność wykonania od góry. Każde issue to jeden PR. Etykiety pochodzą z taksonomii w `SDLC.md`. Dopisek „Zastępuje” wskazuje dotychczasowe issue na GitHubie, które trzeba zaktualizować tym opisem (w nawiasie jego dotychczasowy tytuł).

### 1. Szkielet wtyczki, CI i zasoby lokalne

Etykiety: `feature`, `priority-high`, `risk-low`, `skip-qa`. Zastępuje: #2 (Szkielet + CI).

- [ ] Struktura katalogów z `AGENTS.md` zastępuje obecne pliki w `admin/`, `includes/`, `public/`. Plik główny to `asapchat.php`.
- [ ] `composer install`, `vendor/bin/phpcs`, `vendor/bin/phpunit` i Plugin Check działają lokalnie i w GitHub Actions.
- [ ] W paczce są `admin/fonts/` (Open Sans 400, 600, 700, 800, latin i latin-ext) oraz `admin/img/asapchat-logo-white.svg`.
- [ ] Aktywacja wtyczki nie wysyła żadnego żądania HTTP (test z filtrem `pre_http_request`).

### 2. Ekran wtyczki: rama, nagłówek i trzy stany

Etykiety: `feature`, `priority-high`, `risk-low`, `needs-qa`. Zastępuje: #3 (Strona ustawień w 3 stanach).

- [ ] Strona Ustawienia > AsapChat pokazuje stany „Niepołączony”, „Połączony” i „Błąd” zgodnie z makietą, na danych przykładowych z kontraktu.
- [ ] Nagłówek: czarny pasek, białe logo, fioletowa linia 4 px. W stanie „Połączony” nazwa sklepu, plan, „Otwórz panel AsapChat”, „Rozłącz”.
- [ ] Wszystkie klasy mają prefiks `asapchat-`, reguły są pod `.asapchat-screen`, CSS ładuje się tylko na stronie wtyczki.
- [ ] Ekran działa na 390 px i 1280 px. Karta Sieć nie pokazuje żądań do cudzych domen.

### 3. Klient API i zapis danych

Etykiety: `feature`, `priority-high`, `risk-medium`, `skip-qa`. Zastępuje: #4 (Klient API).

- [ ] Klasa `Asapchat_Api` ma metody dla `POST /token`, `GET /config`, `PATCH /settings`, `DELETE /connection`. Używa `wp_remote_*`, timeout 5 s, nagłówki z punktu 10.
- [ ] Adres bazowy pochodzi ze stałej `ASAPCHAT_BASE`, którą da się nadpisać w `wp-config.php`.
- [ ] Opcje z punktu 5.1 mają poprawny `autoload`. Token nie trafia do `asapchat_config`.
- [ ] Testy PHPUnit na przykładowej odpowiedzi z kontraktu pokrywają kody `200`, `401`, `409`, `410`, `422`, `429`, `500` i brak sieci.

### 4. Połącz przyciskiem

Etykiety: `feature`, `security`, `priority-high`, `risk-high`, `needs-qa`. Zastępuje: #5 (Połącz przyciskiem).

- [ ] Kliknięcie „Połącz z AsapChat” zapisuje `state` i `code_verifier` w transiencie na 2 godziny i przekierowuje na `/connect` z parametrami z punktu 6.
- [ ] Callback sprawdza uprawnienie i `state` przez `hash_equals`, kasuje transient, wymienia kod na token i zapisuje połączenie.
- [ ] „Anuluj”, zły `state`, wygasły transient i `invalid_grant` dają komunikaty z punktu 4.5. Nic nie jest zapisywane.
- [ ] Ścieżka nowego konta (punkt 6.1) kończy się w stanie „Połączony” bez ręcznych kroków.
- [ ] Strona bez https albo z różnymi hostami pokazuje podpowiedź o kluczu instalacyjnym zamiast aktywnego przycisku.

### 5. Klucz instalacyjny

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`. Zastępuje: #6 (Klucz zapasowy).

- [ ] Zwinięte „Mam klucz instalacyjny” z polem i przyciskiem „Połącz kluczem” działa w stanach „Niepołączony” i „Błąd”.
- [ ] Poprawny klucz zapisuje połączenie i config tak samo jak przycisk.
- [ ] Zły format, `401` i `409` dają komunikaty z punktu 7. Klucz nie pojawia się w komunikacie ani w adresie.

### 6. Skrypt czatu i przełącznik „Czat”

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`. Zastępuje: #7 (Skrypt czatu).

- [ ] Tag czatu jest zbudowany z `chat.src` i każdej pary z `chat.attributes` według punktu 8.2.
- [ ] Tag pojawia się tylko, gdy spełnione są warunki z punktu 8.1.
- [ ] Przełącznik „Czat” zapisuje się w `asapchat_settings` i po wyłączeniu tag znika.
- [ ] Obsługa strony sklepu nie wysyła żadnego żądania do AsapChat (test).

### 7. Wyszukiwarka i dodawanie do koszyka

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`. Zastępuje: #8 (Skrypt wyszukiwarki).

- [ ] Tag wyszukiwarki jest wstawiany w `<head>`, gdy `search.enabled` to `true` i przełącznik jest włączony.
- [ ] Przy `search.enabled = false` przełącznik jest nieaktywny, ma wyjaśnienie i link na `sources.connect_url`. Zapisana wartość się nie zmienia.
- [ ] Wyłączone „Dodawanie do koszyka z wyszukiwarki” usuwa z tagu atrybut `allow_add-to-cart`.

### 8. Sekcja „Wygląd” i zapis przez PATCH /settings

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`. Zastępuje: nowe.

- [ ] Karta ma pola: nazwa asystenta, kolor główny (pole koloru i pole tekstowe), pozycja, powitanie 1 i 2. Wartości startowe z `settings` w configu.
- [ ] „Zapisz zmiany” wysyła `PATCH` tylko ze zmienionymi polami i zapisuje zwrócony config.
- [ ] Błędy lokalne i `422` są pokazane przy polach. Wpisane wartości nie znikają.
- [ ] Przy `429`, `5xx` i braku sieci ustawienia lokalne są zapisane, a użytkownik widzi komunikat o niezapisanym wyglądzie.

### 9. Sekcja „Gdzie pokazywać”: opóźnienie i wykluczenia

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`. Zastępuje: nowe.

- [ ] Karta ma pola: opóźnienie (0 do 60), strony, produkty (tylko z WooCommerce), „Adres zawiera”. Wartości są czyszczone według punktu 9.
- [ ] Na wykluczonej stronie, produkcie, stronie sklepu i adresie z pasującym fragmentem nie ma żadnego skryptu.
- [ ] Przy opóźnieniu większym od 0 czat dokłada lokalny plik `public/asapchat-delay.js`, z tymi samymi atrybutami co zwykły tag.
- [ ] Bez WooCommerce ekran i front działają bez błędów PHP.

### 10. Sekcje „Status” i „Źródło zamówień i produktów”

Etykiety: `feature`, `priority-medium`, `risk-low`, `needs-qa`. Zastępuje: nowe.

- [ ] Status czatu i wyszukiwarki pokazuje pigułki i opisy z tabeli w punkcie 4.3, daty w strefie czasowej witryny.
- [ ] Źródła pokazują dwa statusy z `sources` i przycisk „Podłącz w AsapChat” na `sources.connect_url` (nowa karta).
- [ ] Rozwijana instrukcja kluczy WooCommerce ma pięć kroków i link do ekranu kluczy REST API.
- [ ] Bez WooCommerce instrukcja jest ukryta i widać zdanie o bazie wiedzy.

### 11. Odświeżanie configu, błędy i rozłączenie

Etykiety: `feature`, `priority-high`, `risk-medium`, `needs-qa`. Zastępuje: #9 (Odświeżanie, status, rozłączenie).

- [ ] Config odświeża się przy otwarciu ekranu wtyczki i co `refresh_after` sekund przez WP-Cron.
- [ ] `401`, `409`, `410` kasują token i config, zdejmują skrypty i pokazują stan „Błąd” z właściwym opisem.
- [ ] `429`, `5xx` i brak sieci niczego nie kasują. Skrypty działają dalej.
- [ ] „Rozłącz” woła `DELETE /connection`, kasuje połączenie i config, zostawia `asapchat_settings`.

### 12. Usunięcie wtyczki, multisite, HPOS

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`. Zastępuje: #10 (Uninstall, multisite, HPOS).

- [ ] `uninstall.php` próbuje `DELETE /connection`, potem usuwa wszystkie opcje, transienty i zdarzenie crona, także dla każdej witryny w multisite.
- [ ] Dezaktywacja usuwa zdarzenie crona i zostawia dane.
- [ ] Dwie witryny w multisite mają niezależne połączenia.
- [ ] WooCommerce nie pokazuje ostrzeżenia o niezgodności z HPOS.

### 13. Tłumaczenia, readme.txt, grafiki i macierz testów

Etykiety: `documentation`, `priority-medium`, `risk-low`, `needs-qa`. Zastępuje: #11 (Tłumaczenia, readme, grafiki) i #12 (Macierz zgodności).

- [ ] `languages/asapchat.pot` i komplet `pl_PL` pokrywają wszystkie teksty ekranu.
- [ ] `readme.txt` według `docs/marketplace-listing.md` przechodzi walidator i ma sekcję „External services” z faktycznymi adresami.
- [ ] Grafiki z `docs/marketplace-listing.md`, punkt 9, leżą w `.wordpress-org/`.
- [ ] Wszystkie scenariusze z punktu 17 mają wpisany wynik i zrzuty w tym issue.

### 14. Wydanie 1.0.0 i zgłoszenie do WordPress.org

Etykiety: `feature`, `priority-medium`, `risk-medium`, `needs-qa`. Zastępuje: #13 (Wydanie i zgłoszenie).

- [ ] Workflow `release` buduje zip z tagu `v*`, bez plików deweloperskich, poniżej 1 MB.
- [ ] Zip zainstalowany na czystym WordPressie przechodzi Plugin Check bez błędów.
- [ ] Checklista z `docs/marketplace-listing.md`, punkt 10, jest odhaczona w PR.
- [ ] Wtyczka zgłoszona z konta wydawcy z prośbą o slug `asapchat`.
