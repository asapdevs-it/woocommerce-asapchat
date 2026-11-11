# Start dla dewelopera

Czytasz to raz, pierwszego dnia. Potem pracujesz z issues.

## 1. Co budujemy

Oficjalne darmowe wtyczki AsapChat dla platform sklepowych. Każda: łączy sklep z kontem AsapChat, wstawia skrypt czatu i wyszukiwarki, daje ustawienia (wygląd, wykluczenia, opóźnienie), pokazuje status i źródło zamówień oraz produktów.

Repozytoria (organizacja `asapdevs-it`), w kolejności prac:

| Nr | Repo | Gdzie trafia |
|---|---|---|
| 1 | `woocommerce-asapchat` | katalog WordPress.org (slug `asapchat`) |
| 2 | `prestashopasapchat` | zip do pobrania (GitHub Releases, link w panelu AsapChat), bez Addons |
| 3 | `asapchat-gtm-template` | galeria szablonów Google Tag Manager |
| 4 | `asapchat-shoper` | Shoper App Store, aplikacja darmowa |
| później | `asapchat-shopify` | odłożone, nie zaczynamy |
| później | `asapchat-magento` | odłożone, nie zaczynamy |

Backend (`asapchat`) prowadzi Kamil. Kontrakt jest gotowy, działa na produkcji i jest opisany w `docs/CONNECT-API.md`. Nie masz dostępu do repo backendu i go nie potrzebujesz: punkt 7 kontraktu mówi, jak testować.

Nie wysyłamy pytań do supportów platform. Spełniamy opublikowane wymagania i zgłaszamy darmową wtyczkę.

## 2. Kolejność czytania w repo

1. `README.md`
2. `docs/CONNECT-API.md`: jak wtyczka rozmawia z AsapChat
3. `.ai/specs/2026-11-11-connector-v1.md`: co dokładnie ma powstać
4. `docs/mockups/*.html`: otwórz w przeglądarce, tak mają wyglądać ekrany
5. `AGENTS.md`, `SDLC.md`, `CODE_REVIEW.md`: zasady pracy
6. `docs/marketplace-listing.md` i `docs/accounts.md`: wymagania katalogu i konta (potrzebne dopiero pod koniec)

`SDLC.md` jest wspólny dla wszystkich repo firmy i opisuje cały proces po angielsku. Wymienia skille (`om-...`), których nie musisz znać na start: wystarczą te z punktu 4. Wzmianki o `BACKWARD_COMPATIBILITY.md`, `.uxproof/` i `.ai/skills/` nie dotyczą repo wtyczek.

## 3. Narzędzia

1. Zainstaluj `gh` (2.82.1 lub nowszy) i `jq`. Zaloguj się: `gh auth login`.
2. Zainstaluj Claude Code albo Codex CLI.
3. Zainstaluj skille SDLC (raz na komputer):

```bash
npx skills add https://github.com/open-mercato/skills.git --skill '*'
```

4. Narzędzia tego repo: Docker, Node 20, Composer 2 i PHP 8.1 lub nowszy. Do czego służą i jak postawić lokalny sklep: specyfikacja, punkt 21.
5. Sklonuj repo i otwórz w nim agenta. Agent sam czyta `AGENTS.md` i `.ai/agentic.config.json`.

Jako współpracownik repo masz prawo zapisu: tworzysz gałęzie, nadajesz etykiety, otwierasz PR. Do `main` nie da się pisać bezpośrednio.

## 4. Jak wygląda jedno zadanie

1. Wybierz zadanie o najniższym numerze **w tytule** (01, 02, ...), które nie ma etykiety `in-progress` i którego wszystkie zadania z sekcji „Zależności” są zmergowane. Numer issue na GitHubie (`#N`) nie wyznacza kolejności. Tabela zadań z zależnościami: specyfikacja, punkt 20.
2. Powiedz agentowi: `/om-fix <numer issue na GitHubie>` (małe zadanie) albo `/om-auto-create-pr <numer issue na GitHubie>` (większe). Agent przypisze issue do Ciebie, doda `in-progress`, zrobi gałąź, kod, testy i PR.
3. Przeczytaj cały diff, zanim poprosisz o review. Agent myli się pewnym tonem. Ty odpowiadasz za kod.
4. Bramka walidacji (komendy z `.ai/agentic.config.json`) musi przejść lokalnie.
5. PR do `main`. Pierwsze review robi drugi deweloper albo `/om-auto-review-pr <numer PR>`. Każdy PR zatwierdza na końcu Kamil (`prz10`): tego wymaga ochrona gałęzi `main` (plik `.github/CODEOWNERS`). Bez jego akceptacji merge jest zablokowany.
6. PR z `needs-qa` czeka na `qa-approved`. Zmianę przeklikuje **drugi deweloper** (sprawdzacie nawzajem swoje PR) na lokalnym sklepie i dołącza zrzuty ekranu. Własnego PR nie testujesz sam, przy `risk-high` nie ma od tego wyjątków.
7. Merge tylko przez squash. Gałąź `main` wymaga podpisanych commitów, a commit ze squasha podpisuje GitHub, więc swoich commitów nie musisz podpisywać.
8. W opisie PR wpisz `Closes #N` (numer issue na GitHubie, słowo po angielsku). Tylko wtedy GitHub zamknie issue przy merge.

## 5. Zasady, których nie łamiemy

- Zakres v1 to to, co jest w specu. Pomysł na coś więcej: nowe issue, nie dodatkowy kod w PR.
- Żadnych sekretów w repo: tokenów, haseł, kluczy API, danych klientów. Dane dostępowe do kont wydawcy trzymamy w firmowym menedżerze haseł (dostęp: punkt 7).
- Żadnych endpointów diagnostycznych, które coś zwracają bez autoryzacji.
- Gałąź `legacy` to archiwum starego prototypu. Nikt go nie używa, nie kopiujemy z niego niczego.
- Ekrany wtyczki mają logo AsapChat i wygląd panelu AsapChat (tokeny w specu i mockach), nie natywny formularz platformy. Wyjątek: Shopify (Polaris).
- Teksty dla użytkownika: po polsku forma „Ty”, bez pauz (znaku —), krótko. Wersja angielska obowiązkowa tam, gdzie wymaga jej katalog.
- Zanim założysz konto wydawcy na platformie, przeczytaj `docs/accounts.md`. Każde konto ma dać Kamilowi dostęp tak wcześnie, jak platforma pozwala. Jak dokładnie, mówi `docs/accounts.md`.

## 6. Kiedy pytać

Spec i makieta mówią co innego: nie pytaj, obowiązuje reguła ze specyfikacji, punkt 4 (zachowanie i teksty ze specu, układ z makiety).

Pytaj od razu, gdy: platforma wymaga czegoś, czego nie ma w specu, potrzebujesz zmiany w kontrakcie API (etykieta `needs-backend`), coś kosztuje (opłata za konto, płatna wtyczka do testów). Pytanie zadaj jako komentarz w issue i oznacz `prz10`.

## 7. Od Kamila potrzebujesz

Żadna z tych rzeczy nie blokuje zadań 01 do 12. Poproś o nie komentarzem w issue zadania 13 albo 14 (oznacz `prz10`), najlepiej tydzień wcześniej.

| Co | Do czego | Kiedy |
|---|---|---|
| Adres e-mail w domenie firmy (albo alias przekierowany do Ciebie) | konto wydawcy na WordPress.org | przed zadaniem 14 |
| Dostęp do firmowego menedżera haseł | zapis loginu, hasła, kodów 2FA i hasła SVN konta wydawcy | przed zadaniem 14 |
| Potwierdzenie loginu konta wydawcy, gdy `asapcommerce` jest zajęty | loginu WordPress.org nie da się zmienić | przed zadaniem 14 |
| Środowisko GitHub `release` z sekretami SVN | automatyczna publikacja po tagu `v*` | zadanie 14 |
| Licencja płatnej wtyczki do testów (np. WP Rocket) | test nieobowiązkowy, bez licencji go pomijasz | zadanie 13 |
