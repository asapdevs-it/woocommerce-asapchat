# AGENTS.md: wtyczka WordPress `asapchat`

Reguły dla agentów AI i ludzi. Proces: `SDLC.md`. Zakres i zachowanie: `.ai/specs/2026-11-11-connector-v1.md`. Kontrakt backendu: `docs/CONNECT-API.md`.

## Stack

- PHP 7.4 do 8.4, WordPress „latest” i dwie wersje wstecz, WooCommerce opcjonalny.
- Bez Composera w paczce produkcyjnej, bez npm, bez bundlera. Czysty PHP + jeden plik CSS dla panelu.
- Narzędzia deweloperskie: `wp-env` (lokalny WordPress w Dockerze), PHPCS ze standardem `WordPress`, Plugin Check.

## Układ katalogów (do utworzenia w issue 1)

```
asapchat.php                      plik główny: nagłówek, stałe, ładowanie klas, deklaracja HPOS
uninstall.php                     sprzątanie przy usunięciu wtyczki
readme.txt                        opis dla WordPress.org (szkic w docs/marketplace-listing.md)
includes/class-asapchat-api.php       klient HTTP do backendu (token, config, disconnect)
includes/class-asapchat-connect.php   start połączenia, callback, klucz zapasowy, rozłączenie
includes/class-asapchat-admin.php     strona Ustawienia > AsapChat
includes/class-asapchat-frontend.php  wstrzyknięcie skryptów na stronie
admin/settings-page.php           widok ustawień (HTML)
admin/admin.css
languages/                        asapchat.pot, asapchat-pl_PL.po/.mo
.wordpress-org/                   banner, ikona, zrzuty (trafiają do SVN /assets)
tests/                            testy PHPUnit
```

Stare pliki prototypu na `main` usuwa issue 1. Nie dopisuj nic do nich.

## Reguły kodu

1. Prefiks `asapchat_` dla funkcji, opcji, akcji, transientów; klasy `Asapchat_*`. Text domain (identyfikator tłumaczeń): `asapchat`.
2. Każdy plik PHP zaczyna się od `defined( 'ABSPATH' ) || exit;`.
3. Wejście: `sanitize_text_field`, `esc_url_raw`, `absint`. Wyjście: `esc_html`, `esc_attr`, `esc_url`. Bez wyjątków.
4. Każda akcja w panelu: `check_admin_referer()` (nonce, czyli jednorazowy żeton chroniący przed CSRF) i `current_user_can( 'manage_options' )`.
5. HTTP tylko przez `wp_remote_get/post/request`, timeout 5 s. Nigdy cURL.
6. Skrypty przez `wp_enqueue_script` + filtr `script_loader_tag`, który składa tag z atrybutów z configu. Nigdy `echo '<script>'` w `wp_head`.
7. **Żadnego połączenia z asapchat.io przed kliknięciem „Połącz” lub wklejeniem klucza.** Świeżo aktywowana wtyczka nie wysyła niczego.
8. Żadnych iframe w panelu admina. Połączenie to przekierowanie całej karty.
9. Token połączenia w opcji `asapchat_connection` z `autoload = no`. Nigdy w HTML strony, w logach, w komunikatach błędów.
10. Żądania do backendu nigdy podczas obsługi strony sklepu. Front czyta tylko zapisaną opcję `asapchat_config`.
11. Deklaracja zgodności z HPOS w `asapchat.php`. Wtyczka musi działać także bez WooCommerce.
12. `uninstall.php` usuwa wszystkie opcje, transienty i zadanie crona (także na multisite, dla każdej witryny).
13. Kod i domyślne teksty po angielsku, tłumaczenie `pl_PL` w `languages/`. Bez pauz (długi myślnik, U+2014) w tekstach UI.
14. Stable tag w `readme.txt` = wersja w nagłówku `asapchat.php`.

## Walidacja (musi przejść przed PR)

```
composer install                      # tylko narzędzia dev
vendor/bin/phpcs                      # standard WordPress + PHPCompatibilityWP
vendor/bin/phpunit
npx wp-env start && npx wp-env run cli wp plugin check asapchat
```

## Agentowi nie wolno

- Przenosić kodu z gałęzi `legacy` (pull API, `handle_main_route`, klucz „baer”).
- Dodawać synchronizacji zamówień, produktów, webhooków, statystyk.
- Dodawać formularza z e-mailem i hasłem AsapChat.
- Dodawać endpointów debugowych, `var_dump`, `error_log` z tokenem.
- Ładować zasobów z cudzych CDN, dodawać własnego mechanizmu aktualizacji, zaciemniać kodu.
- Zmieniać kontraktu API po swojej stronie. Rozjazd z `docs/CONNECT-API.md` zgłoś w issue.
- Commitować haseł, tokenów, plików `.env`, `.DS_Store`.

## Gdy coś jest niejasne

Zatrzymaj się i dopisz pytanie w issue. Sekcja „Otwarte pytania” w specyfikacji jest listą rzeczy świadomie nierozstrzygniętych.
