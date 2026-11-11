# Specyfikacja: wtyczka WordPress `asapchat`, wersja 1

Data: 2026-11-11. Status: zatwierdzona do realizacji. Fala 1.

## 1. Cel

Właściciel strony na WordPressie instaluje wtyczkę z katalogu WordPress.org, klika jeden przycisk i ma na stronie czat AsapChat oraz (jeśli jego sklep ma zaimportowane produkty) wyszukiwarkę AsapSearch. Bez wklejania kodu, bez edycji motywu.

Miara sukcesu: od aktywacji wtyczki do widocznego czatu na stronie w mniej niż 2 minuty dla osoby, która ma już konto AsapChat.

## 2. Słownik

- **Backend**: serwer AsapChat pod `panel.asapchat.io`.
- **Token połączenia**: tajny ciąg `ack_…`, którym wtyczka przedstawia się backendowi. Wydawany raz, przy połączeniu.
- **Klucz instalacyjny**: ten sam token `ack_…`, ale skopiowany ręcznie z panelu AsapChat (ścieżka zapasowa).
- **Config**: odpowiedź JSON z `GET /api/plugin/v1/config`. Zawiera adresy skryptów i ich atrybuty.
- **PKCE**: zabezpieczenie wymiany kodu. Wtyczka losuje sekret (`code_verifier`), do panelu wysyła tylko jego skrót (`code_challenge`), a sekret pokazuje dopiero przy wymianie kodu na token. Kto przechwyci sam kod, nic z nim nie zrobi.
- **Transient**: wpis tymczasowy w WordPressie z czasem ważności (`set_transient`).
- **HPOS**: nowy sposób przechowywania zamówień w WooCommerce. Wtyczka musi zadeklarować zgodność, nawet jeśli nie dotyka zamówień.

## 3. Historyjki użytkownika

1. Jako właściciel sklepu z kontem AsapChat chcę kliknąć „Połącz” i wybrać sklep, żeby czat pojawił się na stronie.
2. Jako nowa osoba bez konta chcę z tego samego przycisku trafić do rejestracji i po niej wrócić do WordPressa.
3. Jako osoba, u której przekierowanie nie działa (np. zapora), chcę wkleić klucz instalacyjny.
4. Jako właściciel chcę osobno wyłączyć czat albo wyszukiwarkę bez rozłączania konta.
5. Jako właściciel chcę widzieć, czy widget faktycznie działa na stronie.
6. Jako właściciel chcę rozłączyć wtyczkę i mieć pewność, że skrypty znikają.

## 4. Ekran i stany

Jedna strona: **Ustawienia > AsapChat** (`options-general.php?page=asapchat`), uprawnienie `manage_options`. Makieta: `docs/mockups/settings.html`.

### 4.1 Stan „Niepołączony”

- Nagłówek, jedno zdanie opisu.
- Informacja o zgodzie: „Po kliknięciu przejdziesz do panelu AsapChat. Wtyczka połączy się z asapchat.io dopiero wtedy.” plus linki do polityki prywatności i regulaminu.
- Przycisk główny „Połącz z AsapChat”.
- Rozwijane „Mam klucz instalacyjny”: pole tekstowe + „Połącz kluczem”.
- Link „Nie masz konta? Załóż je za darmo” prowadzi tym samym przepływem (panel sam pokaże rejestrację).

### 4.2 Stan „Połączony”

- Pasek statusu: „Połączono ze sklepem {nazwa}”, plan (np. „Free, 50 rozmów AI miesięcznie”).
- Przełącznik „Czat AsapChat na stronie” (domyślnie włączony po połączeniu).
- Przełącznik „Wyszukiwarka AsapSearch” (domyślnie włączony, jeśli `search.enabled = true`).
- Gdy `search.enabled = false`: przełącznik wyszarzony + podpowiedź „Wyszukiwarka będzie dostępna, gdy zaimportujesz produkty w panelu AsapChat.” z linkiem `links.settings`.
- Status instalacji: „Widget widziany na stronie: tak / jeszcze nie” (pola `status.widget_seen_at` i `status.search_seen_at` z configu, `null` = jeszcze nie).
- „Ostatnie odświeżenie ustawień: {data}” + przycisk „Odśwież teraz”.
- „Otwórz panel AsapChat” (`links.panel`, nowa karta) i „Rozłącz” (z potwierdzeniem na osobnym ekranie lub przez `confirm` po stronie WordPressa).

### 4.3 Stan „Błąd, połącz ponownie”

Pokazywany, gdy backend odpowiedział 401 (token odwołany) albo 410 (sklep usunięty).

- Komunikat: „Połączenie z AsapChat wygasło. Skrypty nie są dodawane do strony.”
- Przycisk „Połącz ponownie” (ten sam przepływ co „Połącz”) i „Usuń połączenie”.

### 4.4 Komunikaty jednorazowe

Po powrocie z panelu: sukces („Połączono”), anulowanie („Połączenie anulowane”), błąd („Nie udało się połączyć: {powód}. Spróbuj ponownie albo użyj klucza instalacyjnego.”). Standardowe `add_settings_error` / notice, zamykane.

## 5. Dane zapisywane przez wtyczkę

| Opcja | Zawartość | autoload |
|---|---|---|
| `asapchat_connection` | `token`, `shop_id`, `shop_name`, `connected_at`, `status` (`ok` lub `revoked`) | no |
| `asapchat_config` | ostatni poprawny config + `fetched_at` | yes (czytany na każdej stronie) |
| `asapchat_settings` | `chat_enabled`, `search_enabled` (bool) | yes |

Transient `asapchat_connect_{user_id}`: `state` + `code_verifier`, ważny 10 minut.

Token nigdy nie trafia do `asapchat_config` ani do HTML.

## 6. Przepływ „Połącz” (krok po kroku)

Szczegóły pól: `docs/CONNECT-API.md`.

1. Formularz POST do `admin-post.php` z `action=asapchat_connect_start` i nonce.
2. Handler sprawdza `check_admin_referer( 'asapchat_connect' )` i `manage_options`.
3. Losuje `state` (32 znaki, `wp_generate_password( 32, false )`) i `code_verifier` (64 znaki). Zapisuje oba w transiencie `asapchat_connect_{user_id}`.
4. Liczy `code_challenge = base64url( sha256( code_verifier ) )` (base64 bez `=`, z `-` i `_`).
5. Przekierowuje (`wp_redirect`, bo host jest zewnętrzny; adres stały w kodzie) na:
   `https://panel.asapchat.io/connect?platform=wordpress&site_url={home_url()}&return_url={admin_url('admin-post.php?action=asapchat_connect_callback')}&state=…&code_challenge=…&v=1`
6. Użytkownik loguje się w panelu, wybiera sklep, zatwierdza. Panel przekierowuje na `return_url` z `code` i `state` (albo `error=access_denied` i `state`).
7. Handler `admin_post_asapchat_connect_callback`:
   1. `current_user_can( 'manage_options' )`, inaczej `wp_die`.
   2. Odczyt transientu, porównanie `state` przez `hash_equals`. Brak lub niezgodność: komunikat błędu, koniec.
   3. Usunięcie transientu (jednorazowość).
   4. `error` w adresie: komunikat „anulowano”, koniec.
   5. `POST /api/plugin/v1/token` z `code` i `code_verifier`.
   6. Odpowiedź 200: zapis `asapchat_connection` i `asapchat_config`, ustawienie przełączników domyślnych, przekierowanie na stronę ustawień z komunikatem sukcesu.
   7. Inna odpowiedź: komunikat błędu, nic nie zapisujemy.
8. Callback nie ma nonce (wraca z zewnątrz), dlatego zabezpieczeniem są `state` + sesja zalogowanego admina + PKCE.

Wymóg po stronie backendu: host `return_url` musi być równy hostowi `site_url`. Dlatego oba budujemy z tej samej instalacji. Na stronach, gdzie `home_url()` i `admin_url()` mają różne hosty, połączenie przyciskiem się nie uda. Wtedy użytkownik używa klucza (patrz otwarte pytanie 2).

## 7. Klucz instalacyjny (ścieżka zapasowa)

1. Użytkownik kopiuje klucz z panelu AsapChat (karta „Wtyczka” przy sklepie) i wkleja go w pole.
2. Handler `admin_post_asapchat_connect_key`: nonce, uprawnienie, `sanitize_text_field`, walidacja formatu `^ack_[A-Za-z0-9]{48}$`.
3. `GET /api/plugin/v1/config` z `Authorization: Bearer {klucz}` i nagłówkiem `X-Site-Url: {home_url()}`.
4. 200: zapis jak w kroku 7.6 powyżej. 401: „Klucz jest nieprawidłowy lub odwołany.” 409: „Ten klucz jest już przypisany do innej domeny.”

## 8. Wstrzykiwanie skryptów

Wykonuje `Asapchat_Frontend` na `wp_enqueue_scripts`, tylko gdy:

- `asapchat_connection.status === 'ok'`,
- w `asapchat_config` dany blok ma `enabled = true`,
- odpowiedni przełącznik w `asapchat_settings` jest włączony,
- nie jesteśmy w panelu admina, podglądzie Customizera ani w żądaniu REST/AJAX.

Zasady renderowania:

1. `wp_enqueue_script( 'asapchat-chat', $config['chat']['src'], array(), null, true )` (stopka). Wyszukiwarka: `asapchat-search`, w `<head>`.
2. Filtr `script_loader_tag` dla tych dwóch uchwytów zwraca **cały tag zbudowany od nowa** z mapy `attributes`: każdy klucz przez `esc_attr`, wartość `true` daje sam atrybut bez wartości (np. `defer`), `src` przez `esc_url`. Powód: widget czatu wymaga `id="embedded-widget"`, a WordPress nadaje własne `id="{handle}-js"`.
3. Dopuszczone nazwy atrybutów: `^[a-zA-Z_][a-zA-Z0-9_\-]*$`. Inne pomijamy (ochrona na wypadek błędnego configu).
4. Do obu tagów dodajemy `data-no-optimize="1"` i `data-cfasync="false"`, żeby wtyczki cache nie łączyły i nie przepisywały skryptu (widget czyta własne atrybuty z tagu).
5. Wtyczka nie dodaje niczego więcej na froncie: żadnego CSS, żadnego inline JS.

## 9. Odświeżanie configu

- Przy każdym otwarciu strony ustawień (jeśli ostatnie pobranie starsze niż 60 s).
- Raz dziennie przez WP-Cron: zdarzenie `asapchat_refresh_config`, rejestrowane przy połączeniu, usuwane przy rozłączeniu i dezaktywacji.
- Przycisk „Odśwież teraz”.
- **Nigdy** podczas żądania strony sklepu.

Reakcja na odpowiedź:

| Odpowiedź | Co robi wtyczka |
|---|---|
| 200 | Nadpisuje `asapchat_config`, `status = ok` |
| 401 lub 410 | `status = revoked`, przestaje wstrzykiwać, pokazuje stan 4.3 |
| 409 | Jak 401 + komunikat o innej domenie |
| 5xx, timeout, błąd sieci | Zostawia stary config, skrypty działają dalej, w panelu drobna informacja „Nie udało się odświeżyć” |

## 10. Rozłączenie, dezaktywacja, usunięcie

- **Rozłącz**: `DELETE /api/plugin/v1/connection`. Niezależnie od odpowiedzi usuwamy `asapchat_connection`, `asapchat_config`, zdarzenie crona. `asapchat_settings` zostają.
- **Dezaktywacja**: usuwa zdarzenie crona. Dane zostają (ponowna aktywacja przywraca stan).
- **Usunięcie** (`uninstall.php`): próba `DELETE` (best effort, timeout 5 s), potem usunięcie wszystkich opcji i transientów `asapchat_*`. Na multisite pętla po `get_sites()`.

## 11. Multisite

Każda witryna w sieci ma własne połączenie i własne opcje. Brak ustawień na poziomie sieci. Aktywacja sieciowa jest dozwolona i nie robi nic poza udostępnieniem strony ustawień w każdej witrynie.

## 12. WooCommerce

- Wtyczka nie wymaga WooCommerce (czat ma sens na każdej stronie). Nie dodajemy `Requires Plugins`.
- W `asapchat.php` deklaracja HPOS przez `FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true )` na `before_woocommerce_init`.
- Nagłówek `WC tested up to`.

## 13. Tłumaczenia

Teksty źródłowe po angielsku, `__()` / `esc_html__()` z domeną `asapchat`. W repo `languages/asapchat.pot` i komplet `pl_PL`. Polskie teksty wzorcowe są w makiecie. Forma „Ty”, bez pauz.

## 14. Bezpieczeństwo (checklista do review)

- [ ] Brak jakiegokolwiek żądania do asapchat.io przed kliknięciem „Połącz” lub wysłaniem klucza (test automatyczny z filtrem `pre_http_request`).
- [ ] Nonce + `manage_options` na każdej akcji `admin-post` poza callbackiem; callback: `manage_options` + `state` przez `hash_equals`.
- [ ] `state` i `code_verifier` jednorazowe, transient kasowany przed wymianą kodu.
- [ ] Adres panelu i API stałe w kodzie (stała `ASAPCHAT_PANEL_URL`, nadpisywalna tylko przez `define` w `wp-config.php` na potrzeby dev).
- [ ] Token nie pojawia się w HTML, w notice, w logach, w adresie URL.
- [ ] Wszystkie wartości z configu escapowane przy renderze; `src` musi zaczynać się od `https://asapchat.io/` lub `https://panel.asapchat.io/`, inaczej nie renderujemy.
- [ ] Brak endpointów REST, AJAX `nopriv` i parametrów debug.
- [ ] `uninstall.php` sprawdza `WP_UNINSTALL_PLUGIN`.

## 15. Macierz testów

| Oś | Warianty |
|---|---|
| WordPress | najnowszy, najnowszy minus 2 wersje główne |
| PHP | 7.4, 8.1, 8.4 |
| WooCommerce | brak; najnowszy z HPOS włączonym |
| Motyw | Storefront, motyw blokowy (Twenty Twenty-Five), Astra |
| Cache | WP Rocket, LiteSpeed Cache, Autoptimize (każdy z opóźnianiem i łączeniem JS) |
| Zgody | jedna wtyczka cookies (np. Complianz) aktywna |
| Sieć | pojedyncza witryna, multisite (2 witryny, różne sklepy) |

Scenariusze na każdej kombinacji z wiersza „podstawowego” (najnowszy WP, PHP 8.4, Woo, Storefront) i wyrywkowo na pozostałych:

1. Aktywacja: zero żądań wychodzących, brak błędów PHP przy `WP_DEBUG`.
2. Połącz przyciskiem: czat widoczny na stronie głównej, produktowej, w koszyku.
3. Połącz kluczem.
4. Anulowanie w panelu: czytelny komunikat, nic nie zapisane.
5. Przełączniki: wyłączenie czatu usuwa tag, wyszukiwarka zostaje.
6. `search.enabled = false`: przełącznik wyszarzony, brak tagu.
7. 401 z backendu: stan błędu, brak tagów. 500: tagi zostają.
8. Rozłącz: brak tagów, opcje usunięte.
9. Usunięcie wtyczki: `wp option list --search='asapchat*'` pusty.
10. Cache: po włączeniu łączenia JS czat nadal działa i ma poprawne atrybuty w źródle strony.

Środowisko testowe backendu: adres stagingu poda właściciel w issue 3 (stała `ASAPCHAT_PANEL_URL` w `wp-config.php`).

## 16. Definicja ukończenia

- Wszystkie issues zamknięte, CI zielone (PHPCS, PHPUnit, Plugin Check bez błędów).
- Macierz testów przejścia udokumentowana w issue 11 (tabela + zrzuty).
- `readme.txt` przechodzi walidator WordPress.org, zawiera sekcję „External services”.
- Paczka zip < 1 MB, bez plików deweloperskich (`.git`, `tests`, `vendor`, `.ai`, `docs`).
- Kod całej wtyczki da się przeczytać w jednym podejściu (orientacyjnie do 600 linii PHP).
- Właściciel (`prz10`) zatwierdził PR wydania.

## 17. Fazy i issues

| Faza | Issue |
|---|---|
| A. Podstawa | 1 Szkielet + CI |
| B. Panel | 2 Strona ustawień w 3 stanach |
| C. Połączenie | 3 Klient API, 4 Połącz przyciskiem, 5 Klucz zapasowy |
| D. Front | 6 Skrypt czatu, 7 Skrypt wyszukiwarki |
| E. Utrzymanie | 8 Odświeżanie, status, rozłączenie; 9 Uninstall, multisite, HPOS |
| F. Wydanie | 10 Tłumaczenia, readme, grafiki; 11 Macierz zgodności; 12 Wydanie i zgłoszenie |

## 18. Otwarte pytania

1. **Pole „widget widziany na stronie”.** Rozstrzygnięte: config zwraca `status.widget_seen_at` i `status.search_seen_at` (patrz `docs/CONNECT-API.md`).
2. **Różne hosty `home_url()` i `admin_url()`** (rzadkie instalacje typu headless). Decyzja na teraz: tylko klucz zapasowy. Do potwierdzenia.
3. **Slug.** WordPress.org wyprowadza slug z nazwy wtyczki. Przy zgłoszeniu trzeba wprost poprosić o `asapchat`. Jeśli zespół odmówi, zapasowy: `asapchat-ai-chat`.
4. **Wersja „Tested up to”.** Wpisać aktualną w dniu wydania.
5. **Atrybuty a wtyczki cache.** `data-no-optimize` działa w LiteSpeed i WP Rocket; dla Autoptimize może być potrzebny filtr wykluczenia. Rozstrzyga issue 11.
6. **Branding w widgecie.** Wytyczna 10 WordPress.org wymaga, żeby link „Powered by” na froncie był domyślnie wyłączony. Do sprawdzenia, czy widget czatu pokazuje taki link w planie Free i czy to podpada pod tę regułę (do potwierdzenia z zespołem WordPress.org przy review).
