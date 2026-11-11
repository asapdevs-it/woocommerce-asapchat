# AsapChat dla WordPressa (wtyczka `asapchat`)

Oficjalna wtyczka WordPress. Łączy stronę lub sklep WooCommerce z kontem AsapChat i dodaje na stronę dwa skrypty: czat AI (AsapChat) oraz wyszukiwarkę produktów (AsapSearch).

Miejsce publikacji: katalog wtyczek WordPress.org. Slug (stały identyfikator w adresie): `asapchat`. Nazwa wyświetlana: „AsapChat: AI Chat and Product Search”. Wtyczka jest darmowa.

> Nazwa repozytorium na GitHubie (`woocommerce-asapchat`) jest historyczna. Folder wtyczki i plik główny nazywają się `asapchat` / `asapchat.php`. WordPress.org nie pozwala, żeby slug zaczynał się od cudzego znaku towarowego („woocommerce”).

## Co robi wersja 1

1. **Połącz.** Przycisk „Połącz z AsapChat” przenosi właściciela sklepu do panelu AsapChat. Tam loguje się albo zakłada darmowe konto i przechodzi kreator. Potem wraca do WordPressa automatycznie. Wtyczka dostaje tajny token połączenia. Zapasowo: pole na klucz instalacyjny skopiowany z panelu.
2. **Wstaw skrypty.** Wtyczka dodaje na stronę `<script>` czatu i `<script>` wyszukiwarki z atrybutami pobranymi z AsapChat.
3. **Ekran ustawień w stylu AsapChat.** Po połączeniu ekran ma sekcje:
   - nagłówek: logo, nazwa sklepu, plan, link do panelu, „Rozłącz”,
   - status: czy czat i wyszukiwarka były widziane na stronie,
   - widgety: przełączniki „Czat”, „Wyszukiwarka AsapSearch”, „Dodawanie do koszyka z wyszukiwarki”,
   - wygląd: nazwa asystenta, kolor, pozycja, dwa powitania (zapis w AsapChat, te same wartości co w panelu),
   - gdzie pokazywać: opóźnienie czatu i wykluczenia stron, produktów, fragmentów adresu,
   - źródło zamówień i produktów: dwa statusy, przycisk „Podłącz w AsapChat” i instrukcja kluczy API WooCommerce.

## Czego wersja 1 nie robi

- Nie synchronizuje zamówień ani produktów. AsapChat pobiera je sam przez REST API WooCommerce, po zgodzie właściciela w panelu.
- Nie wystawia własnego API ani endpointów diagnostycznych.
- Nie pokazuje statystyk, rozmów ani bazy wiedzy. To jest w panelu AsapChat.
- Nie prosi o hasło do AsapChat w panelu WordPressa.
- Nie ma własnego mechanizmu aktualizacji. Wtyczkę aktualizuje WordPress.org.

## Status

- Kontrakt z AsapChat jest gotowy i działa na produkcji: `docs/CONNECT-API.md`. Punkt 7 mówi, jak założyć konto testowe i co da się sprawdzić na `localhost`.
- Lokalny sklep krok po kroku (`@wordpress/env`, WooCommerce, dane przykładowe): specyfikacja, punkt 21.4.
- Lista zadań: specyfikacja, punkt 20. Kolejność wyznacza numer w tytule zadania (01 do 14), nie numer issue na GitHubie.
- Na `main` leży jeszcze stary prototyp (`admin/`, `includes/`, `public/`, `woocommerce-asapchat.php`). Usuwa go zadanie 01. Nie dopisuj do niego i nie kopiuj z niego kodu.
- Gałąź `legacy` to archiwum tego prototypu, gałąź `master` to stara pozostałość. Obie ignoruj.
- To repozytorium jest publiczne. Nie wpisuj tu niczego wewnętrznego: haseł, danych klientów, adresów prywatnych systemów.

## Zacznij tutaj (kolejność czytania)

1. `ONBOARDING-DEV.md`: jak ustawić narzędzia i jak wygląda praca z zadaniem i PR.
2. `docs/CONNECT-API.md`: kontrakt z AsapChat. Przeczytaj cały.
3. `.ai/specs/2026-11-11-connector-v1.md`: pełna specyfikacja wtyczki, lista zadań (punkt 20), narzędzia i lokalny sklep (punkt 21), teksty EN i PL (punkt 22).
4. `docs/mockups/settings.html`: makieta ekranu ustawień (otwórz w przeglądarce, przełącz stan u góry).
5. `AGENTS.md`, `SDLC.md`, `CODE_REVIEW.md`: reguły dla agenta AI i dla Ciebie, proces od zadania do zmergowanego PR.
6. `docs/marketplace-listing.md` i `docs/accounts.md`: teksty i grafiki do katalogu, konto wydawcy. Potrzebne dopiero w zadaniach 13 i 14.
7. Issues w tym repo, w kolejności numeru z tytułu (01, 02, ...).

## Linki

- Panel AsapChat: https://panel.asapchat.io
- Strona produktu: https://asapchat.io
- Wytyczne WordPress.org: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- Plugin Check (narzędzie sprawdzające wtyczkę przed zgłoszeniem): https://wordpress.org/plugins/plugin-check/
