# AsapChat dla WordPressa (wtyczka `asapchat`)

Oficjalna wtyczka WordPress, która łączy stronę lub sklep WooCommerce z kontem AsapChat i dodaje na stronę dwa skrypty: czat AI (AsapChat) oraz wyszukiwarkę produktów (AsapSearch).

Docelowe miejsce publikacji: katalog wtyczek WordPress.org, slug (stały identyfikator w adresie) `asapchat`, nazwa wyświetlana „AsapChat: AI Chat and Product Search”.

> Nazwa repozytorium na GitHubie (`woocommerce-asapchat`) jest historyczna. Folder wtyczki i plik główny nazywają się `asapchat` / `asapchat.php`. WordPress.org nie pozwala, żeby slug zaczynał się od cudzego znaku towarowego („woocommerce”).

## Co robi wersja 1 (i nic więcej)

1. **Połącz.** Przycisk „Połącz z AsapChat” przenosi właściciela sklepu do panelu AsapChat. Tam loguje się lub rejestruje, wybiera sklep i wraca do WordPressa. Wtyczka dostaje tajny token połączenia. Zapasowo: pole na klucz instalacyjny skopiowany z panelu.
2. **Wstrzyknij skrypty.** Wtyczka dodaje na stronę `<script>` czatu i `<script>` wyszukiwarki z atrybutami pobranymi z backendu AsapChat. Każdy ma osobny przełącznik.
3. **Pokaż status.** Nazwa połączonego sklepu, plan, czy widget był już widziany na stronie, link do panelu, przycisk „Rozłącz”.

## Czego wersja 1 nie robi

- Nie synchronizuje zamówień ani produktów. Backend AsapChat robi to sam przez REST API WooCommerce.
- Nie wystawia własnego API (stary endpoint `asapchat/api` z gałęzi `legacy` nie wraca).
- Nie pokazuje statystyk, rozmów ani ustawień wyglądu czatu. To jest w panelu AsapChat.
- Nie prosi o hasło do AsapChat w panelu WordPressa.

## Status

- **Fala 1.** Start kodu jest zablokowany tylko przez kontrakt backendu (endpointy `/connect` i `/api/plugin/v1/*`). Opis kontraktu: `docs/CONNECT-API.md`.
- Gałąź `legacy` zawiera prototyp z 2024 roku. Służy wyłącznie jako archiwum. Nie kopiuj z niej kodu.

## Zacznij tutaj (kolejność czytania)

1. `ONBOARDING-DEV.md`: jak ustawić narzędzia i jak wygląda praca z issue i PR.
2. `SDLC.md`: proces od zadania do zmergowanego PR.
3. `.ai/specs/2026-11-11-connector-v1.md`: pełna specyfikacja wtyczki.
4. `docs/CONNECT-API.md`: kontrakt z backendem AsapChat.
5. `docs/mockups/settings.html`: makieta ekranu ustawień (otwórz w przeglądarce).
6. `AGENTS.md`: reguły dla agenta AI i dla Ciebie.
7. `docs/accounts.md`: konto wydawcy na WordPress.org.
8. `docs/marketplace-listing.md`: teksty i grafiki do katalogu, checklista zgłoszenia.
9. Issues w tym repo, od numeru 1.

## Linki

- Panel AsapChat: https://panel.asapchat.io
- Strona produktu: https://asapchat.io
- Wytyczne WordPress.org: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- Plugin Check (narzędzie sprawdzające wtyczkę przed zgłoszeniem): https://wordpress.org/plugins/plugin-check/
