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

- Kontrakt z AsapChat jest gotowy: `docs/CONNECT-API.md`. Punkt 7 mówi, jak założyć konto testowe i postawić lokalny sklep.
- Lista zadań w kolejności wykonania: ostatni punkt specyfikacji („Issues”).
- Gałąź `legacy` to archiwum. Nie kopiuj z niej kodu.

## Zacznij tutaj (kolejność czytania)

1. `ONBOARDING-DEV.md`: jak ustawić narzędzia i jak wygląda praca z issue i PR.
2. `SDLC.md`: proces od zadania do zmergowanego PR.
3. `docs/CONNECT-API.md`: kontrakt z AsapChat. Przeczytaj cały.
4. `.ai/specs/2026-11-11-connector-v1.md`: pełna specyfikacja wtyczki i lista issues.
5. `docs/mockups/settings.html`: makieta ekranu ustawień (otwórz w przeglądarce, przełącz stan u góry).
6. `AGENTS.md`: reguły dla agenta AI i dla Ciebie.
7. `docs/accounts.md`: konto wydawcy na WordPress.org.
8. `docs/marketplace-listing.md`: teksty i grafiki do katalogu, checklista zgłoszenia.
9. Issues w tym repo, w kolejności numerów.

## Linki

- Panel AsapChat: https://panel.asapchat.io
- Strona produktu: https://asapchat.io
- Wytyczne WordPress.org: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- Plugin Check (narzędzie sprawdzające wtyczkę przed zgłoszeniem): https://wordpress.org/plugins/plugin-check/
