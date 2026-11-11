# AGENTS.md: wtyczka WordPress `asapchat`

Reguły dla agentów AI i ludzi. Proces: `SDLC.md`. Zakres i zachowanie: `.ai/specs/2026-11-11-connector-v1.md`. Kontrakt z AsapChat: `docs/CONNECT-API.md`.

## Zakres w jednym akapicie

Wtyczka łączy stronę z kontem AsapChat, wstawia skrypt czatu i wyszukiwarki, i pokazuje ekran ustawień w stylu AsapChat: status, przełączniki widgetów, wygląd (zapis przez `PATCH /settings`), opóźnienie i wykluczenia, źródło zamówień i produktów. Nic poza tym.

## Kolejność pracy

1. Przeczytaj `docs/CONNECT-API.md` w całości. Punkt 7 mówi, jak założyć konto testowe i postawić lokalny sklep.
2. Przeczytaj specyfikację i otwórz makietę `docs/mockups/settings.html`.
3. Bierz issues w kolejności z ostatniego punktu specyfikacji. Jedno issue to jeden PR.
4. Kontrakt jest gotowy i nie zmieniasz go po swojej stronie. Potrzebujesz nowego pola: issue z etykietą `needs-backend`.

## Stack

- PHP 7.4 do 8.4, WordPress „latest” i dwie wersje wstecz, WooCommerce opcjonalny.
- Bez Composera w paczce produkcyjnej, bez npm, bez bundlera. Czysty PHP, jeden plik CSS panelu, dwa krótkie pliki JS (panel i opóźnienie czatu), fonty Open Sans w paczce.
- Narzędzia deweloperskie: `wp-env` (lokalny WordPress w Dockerze), PHPCS ze standardem `WordPress`, Plugin Check.

## Układ katalogów (tworzy issue 1)

```
asapchat.php                      plik główny: nagłówek, stałe, ładowanie klas, deklaracja HPOS
uninstall.php                     sprzątanie przy usunięciu wtyczki
readme.txt                        opis dla WordPress.org (szkic w docs/marketplace-listing.md)
includes/class-asapchat-api.php       klient HTTP: token, config, settings, disconnect
includes/class-asapchat-connect.php   start połączenia, callback, klucz zapasowy, rozłączenie
includes/class-asapchat-admin.php     strona Ustawienia > AsapChat, zapis formularza
includes/class-asapchat-frontend.php  wstawianie skryptów, wykluczenia, opóźnienie
admin/settings-page.php           widok ekranu (HTML)
admin/admin.css                   styl ekranu, wszystko pod .asapchat-screen
admin/admin.js                    synchronizacja pola koloru z polem tekstowym
admin/fonts/                      Open Sans 400/600/700/800, woff2, latin i latin-ext
admin/img/                        logo AsapChat (SVG)
public/asapchat-delay.js          dokłada skrypt czatu po opóźnieniu
languages/                        asapchat.pot, asapchat-pl_PL.po/.mo
.wordpress-org/                   banner, ikona, zrzuty (trafiają do SVN /assets)
tests/                            testy PHPUnit
```

Issue 1 zastępuje obecne pliki w `admin/`, `includes/` i `public/` tym układem. Nie dopisuj nic do obecnych plików.

## Reguły kodu

1. Prefiks `asapchat_` dla funkcji, opcji, akcji, transientów. Klasy `Asapchat_*`. Text domain (identyfikator tłumaczeń): `asapchat`.
2. Każdy plik PHP zaczyna się od `defined( 'ABSPATH' ) || exit;`.
3. Wejście: `sanitize_text_field`, `esc_url_raw`, `absint`. Wyjście: `esc_html`, `esc_attr`, `esc_url`. Bez wyjątków.
4. Każda akcja w panelu: `check_admin_referer()` (nonce, czyli jednorazowy żeton chroniący przed CSRF) i `current_user_can( 'manage_options' )`.
5. HTTP tylko przez `wp_remote_get/post/request`, timeout 5 s. Nigdy cURL.
6. Skrypty widgetów przez `wp_enqueue_script` i filtr `script_loader_tag`, który składa tag z atrybutów z configu. Nigdy `echo '<script>'` w `wp_head`. Jedyny wyjątek od zwykłego tagu to opóźnienie czatu: lokalny `public/asapchat-delay.js` z danymi przez `wp_add_inline_script`.
7. **Żadnego połączenia z AsapChat przed kliknięciem „Połącz” lub wklejeniem klucza.** Świeżo aktywowana wtyczka nie wysyła niczego.
8. Żadnych iframe w panelu admina. Połączenie to przekierowanie całej karty.
9. Token połączenia w opcji `asapchat_connection` z `autoload = no`. Nigdy w HTML strony, w JS, w logach, w komunikatach błędów.
10. Żądania do AsapChat nigdy podczas obsługi strony sklepu. Front czyta tylko zapisane opcje `asapchat_config` i `asapchat_settings`.
11. `state` i `code_verifier` w transiencie na 2 godziny. Nie skracaj tego czasu.
12. Adres AsapChat w jednej stałej `ASAPCHAT_BASE`, nadpisywalnej w `wp-config.php`.
13. CSS panelu: klasy z prefiksem `asapchat-`, każda reguła pod `.asapchat-screen`, plik ładowany tylko na stronie wtyczki. Fonty i logo z paczki, żadnych zdalnych zasobów w panelu.
14. Deklaracja zgodności z HPOS w `asapchat.php`. Wtyczka musi działać także bez WooCommerce.
15. `uninstall.php` usuwa wszystkie opcje, transienty i zadanie crona (także na multisite, dla każdej witryny).
16. Kod i domyślne teksty po angielsku, tłumaczenie `pl_PL` w `languages/`. Forma „Ty”. Bez pauz (długi myślnik, U+2014) w tekstach UI.
17. Stable tag w `readme.txt` = wersja w nagłówku `asapchat.php`.

## Walidacja (musi przejść przed PR)

```
composer install                      # tylko narzędzia dev
vendor/bin/phpcs                      # standard WordPress + PHPCompatibilityWP
vendor/bin/phpunit
npx wp-env start && npx wp-env run cli wp plugin check asapchat
```

Przed każdym PR przeklikaj też przypadki z `docs/CONNECT-API.md`, punkt 7.5.

## Agentowi nie wolno

- Kopiować kodu z gałęzi `legacy`. To archiwum.
- Dodawać synchronizacji zamówień, produktów, webhooków, statystyk.
- Tworzyć kluczy API WooCommerce ani wysyłać ich do AsapChat. Źródła łączy właściciel w panelu AsapChat.
- Dodawać formularza z e-mailem i hasłem AsapChat.
- Dodawać endpointów diagnostycznych, `var_dump`, `error_log` z tokenem.
- Ładować zasobów z cudzych CDN (także fontów), dodawać własnego mechanizmu aktualizacji albo paska „dostępna nowa wersja”, zaciemniać kodu.
- Zmieniać kontraktu API po swojej stronie. Rozjazd z `docs/CONNECT-API.md` zgłoś w issue z etykietą `needs-backend`.
- Commitować haseł, tokenów, plików `.env`, `.DS_Store`.

## Gdy coś jest niejasne

Zatrzymaj się i dopisz pytanie w issue. Gdy specyfikacja i kontrakt mówią co innego, wygrywa kontrakt.
