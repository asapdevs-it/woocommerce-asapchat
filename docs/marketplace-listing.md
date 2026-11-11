# WordPress.org: treść wpisu, grafiki, zgłoszenie

Wszystko, co trzeba wkleić lub wgrać, żeby wtyczka pojawiła się w katalogu WordPress.org. Pozycje oznaczone „do potwierdzenia” sprawdź w dniu zgłoszenia.

## 1. Dane podstawowe

| Pole | Wartość |
|---|---|
| Nazwa wtyczki (Plugin Name) | AsapChat: AI Chat and Product Search |
| Slug | `asapchat` (poproś o niego wprost w formularzu; zapasowy `asapchat-ai-chat`) |
| Autor | AsapCommerce |
| Licencja | GPLv2 or later |
| Cena | bezpłatna |
| Text domain | `asapchat` |

Zasady nazwy: nie zaczyna się od cudzej marki, nie zawiera słów „WordPress” ani „plugin”. „for WooCommerce” na końcu jest dozwolone, ale nie używamy, bo wtyczka działa też bez WooCommerce.

## 2. Krótki opis (maks. 150 znaków, bez formatowania)

- EN (readme.txt): `Connect your site to AsapChat in one click: AI chat that answers customers and a fast product search for your store.` (118 znaków)
- PL (tłumaczenie): `Połącz stronę z AsapChat jednym kliknięciem: czat AI, który odpowiada klientom, i szybka wyszukiwarka produktów.` (113 znaków)

## 3. Tagi (maks. 5, bez nazw konkurencji)

`live chat, ai chatbot, product search, woocommerce, customer support`

## 4. Długi opis EN

```
AsapChat adds two things to your WordPress site:

* **AI chat** that answers customer questions using your own knowledge base, products and order statuses, and hands the conversation to a human when needed.
* **AsapSearch**, a fast, typo-tolerant product search for WooCommerce stores.

This plugin is the official connector. It does one job: it links your site to your AsapChat account and adds the AsapChat scripts to your pages. No theme editing, no code to paste.

**How it works**

1. Install and activate the plugin.
2. Go to Settings > AsapChat and click "Connect to AsapChat".
3. Log in or create a free account, pick your shop, and you are back in WordPress.
4. The chat appears on your site. Turn the chat or the search on and off with two switches.

**What you need**

A free AsapChat account. The Free plan includes 50 AI conversations per month. Everything else (chat appearance, knowledge base, team inbox) is managed in the AsapChat panel.

**Good to know**

* Works with or without WooCommerce.
* Compatible with WooCommerce High-Performance Order Storage (HPOS).
* The plugin does not contact AsapChat until you click Connect.
* Removing the plugin removes all its data from your database.
```

## 5. Długi opis PL (do tłumaczenia na translate.wordpress.org po akceptacji)

```
AsapChat dodaje do Twojej strony na WordPressie dwie rzeczy:

* **Czat AI**, który odpowiada klientom na podstawie Twojej bazy wiedzy, produktów i statusów zamówień, a gdy trzeba, przekazuje rozmowę człowiekowi.
* **AsapSearch**, szybką wyszukiwarkę produktów dla sklepów WooCommerce, odporną na literówki.

Ta wtyczka to oficjalny łącznik. Robi jedną rzecz: łączy stronę z Twoim kontem AsapChat i dodaje skrypty AsapChat do stron. Bez edycji motywu i bez wklejania kodu.

**Jak to działa**

1. Zainstaluj i włącz wtyczkę.
2. Wejdź w Ustawienia > AsapChat i kliknij „Połącz z AsapChat”.
3. Zaloguj się lub załóż darmowe konto, wybierz sklep i wracasz do WordPressa.
4. Czat pojawia się na stronie. Czat i wyszukiwarkę włączasz i wyłączasz dwoma przełącznikami.

**Czego potrzebujesz**

Darmowego konta AsapChat. Plan Free obejmuje 50 rozmów AI miesięcznie. Wygląd czatu, baza wiedzy i skrzynka zespołu są w panelu AsapChat.
```

## 6. FAQ (EN, do readme.txt)

1. **Do I need an AsapChat account?** Yes. You can create a free one during the connection step.
2. **Is it free?** The plugin is free. AsapChat has a Free plan (50 AI conversations per month) and paid plans. See asapchat.io for current pricing.
3. **Does it work without WooCommerce?** Yes. The chat works on any WordPress site. The product search needs a store with imported products.
4. **The chat does not show up. What should I check?** Make sure the site address in your AsapChat shop matches this site, clear your cache plugin, and check that the Chat switch is on in Settings > AsapChat.
5. **I use a cache or optimization plugin.** The scripts are marked to be skipped by JS optimization. If your plugin still combines them, exclude `asapchat.io` from JS optimization.
6. **What happens when I disconnect or delete the plugin?** The scripts are removed from your pages immediately and the plugin deletes its options. Your AsapChat account and data stay intact.
7. **Where do I change the chat colors and messages?** In the AsapChat panel. The plugin picks up changes within a day, or immediately when you open its settings page.

## 7. Sekcja „External services” (obowiązkowa, EN, do readme.txt)

Brak tej sekcji to jeden z najczęstszych powodów odrzucenia.

```
== External services ==

This plugin connects your site to AsapChat (https://asapchat.io), a service provided by AsapCommerce. It is required for the plugin to work: the chat and the product search are delivered by this service.

Nothing is sent before you click "Connect to AsapChat" or submit an install key.

1. Connecting (when you click Connect or submit an install key, and when you disconnect):
   the plugin sends your site URL, a one-time code and a code verifier to https://panel.asapchat.io, and receives a connection token.
2. Configuration refresh (when you open the plugin settings page, and once a day):
   the plugin sends the connection token and your site URL to https://panel.asapchat.io/api/plugin/v1/config and receives the script addresses and their attributes.
3. On your storefront (on every page, while connected and enabled):
   the visitor's browser loads the chat script from https://asapchat.io/chat/ and the search script from https://asapchat.io/search/. These scripts communicate with https://panel.asapchat.io to deliver chat messages and search results. Messages typed by the visitor and search queries are sent to the service.

Terms of service: https://asapchat.io/regulamin
Privacy policy: https://asapchat.io/privacy-policy (Polish: https://asapchat.io/polityka-prywatnosci)
```

Przed zgłoszeniem sprawdź, czy trzy adresy powyżej odpowiadają 200 i czy lista hostów zgadza się z tym, co faktycznie ładuje widget (narzędzia deweloperskie przeglądarki, zakładka Network).

## 8. Szkic `readme.txt`

Plik musi nazywać się dokładnie `readme.txt` (małe litery). Walidator: https://wordpress.org/plugins/developers/readme-validator/

```
=== AsapChat: AI Chat and Product Search ===
Contributors: asapcommerce
Tags: live chat, ai chatbot, product search, woocommerce, customer support
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your site to AsapChat in one click: AI chat that answers customers and a fast product search for your store.

== Description ==

(sekcja 4)

== Installation ==

1. Install the plugin from the Plugins screen and activate it.
2. Go to Settings > AsapChat.
3. Click "Connect to AsapChat", log in or sign up, and choose your shop.
4. Alternatively, paste an install key from your AsapChat panel under "I have an install key".

== Frequently Asked Questions ==

(sekcja 6, każde pytanie jako "= Pytanie =")

== External services ==

(sekcja 7)

== Screenshots ==

1. Not connected: one button to connect your site.
2. Connected: shop name, plan and switches for chat and search.
3. The AsapChat widget on a storefront.
4. AsapSearch results on a WooCommerce store.

== Changelog ==

= 1.0.0 =
* First release.
```

Uwagi: `Contributors` to login konta WordPress.org (do potwierdzenia po założeniu konta). `Tested up to` to tylko numer wersji głównej, aktualny w dniu wydania. `Requires at least` ustawiamy na „najnowsza minus 2” w dniu wydania. `Stable tag` nigdy `trunk`.

## 9. Grafiki (katalog `.wordpress-org/` w repo, w SVN trafiają do `/assets/`, nie do `trunk`)

| Plik | Rozmiar | Limit | Uwagi |
|---|---|---|---|
| `banner-772x250.png` | 772 x 250 px | 4 MB | logo AsapChat + hasło, fiolet #6543f0 |
| `banner-1544x500.png` | 1544 x 500 px | 4 MB | wersja dla ekranów o wysokiej gęstości |
| `icon-128x128.png` | 128 x 128 px | 1 MB | sam znak, bez tekstu |
| `icon-256x256.png` | 256 x 256 px | 1 MB | |
| `screenshot-1.png` | np. 1280 x 800 | 10 MB | stan „Niepołączony” |
| `screenshot-2.png` | np. 1280 x 800 | 10 MB | stan „Połączony” |
| `screenshot-3.png` | np. 1280 x 800 | 10 MB | czat na sklepie demo |
| `screenshot-4.png` | np. 1280 x 800 | 10 MB | wyniki wyszukiwarki na sklepie demo |

Numer w nazwie pliku odpowiada numerowi linii w sekcji `== Screenshots ==`. Nazwy plików małymi literami. Na zrzutach żadnych danych prawdziwych klientów. Logo dostarcza właściciel (plik SVG z repo backendu, `asapchat-logo-white.svg` i wersja ciemna).

## 10. Wymagania platformy (checklista przed zgłoszeniem)

- [ ] Cały kod i grafiki na licencji zgodnej z GPL.
- [ ] Kod czytelny, bez zaciemniania. Wtyczka nie zawiera zminifikowanego JS.
- [ ] Nic we wtyczce nie jest blokowane po okresie próbnym (limity planu egzekwuje usługa, nie wtyczka).
- [ ] Usługa zewnętrzna opisana w readme (sekcja 7).
- [ ] Zero żądań do zewnętrznych serwerów przed zgodą (kliknięcie „Połącz”).
- [ ] Skrypty ładowane tylko z własnej usługi (`asapchat.io`), żadnych cudzych CDN.
- [ ] Brak iframe w panelu admina, brak własnego mechanizmu aktualizacji.
- [ ] Komunikaty w panelu zamykane, tylko na stronie wtyczki.
- [ ] Maks. 5 tagów, bez nazw konkurencji.
- [ ] Prefiks `asapchat_` wszędzie, `ABSPATH` w każdym pliku.
- [ ] Sanityzacja wejścia, escapowanie wyjścia, nonce, sprawdzenie uprawnień.
- [ ] `wp_remote_*` zamiast cURL, brak składni HEREDOC.
- [ ] Plugin Check: zero błędów w kategorii „Plugin Repo”.
- [ ] Zip < 10 MB, bez plików deweloperskich.
- [ ] Deklaracja HPOS, nagłówek `WC tested up to`.
- [ ] Konto zgłaszające ma 2FA.

## 11. Typowe powody odrzucenia

1. Nieopisana usługa zewnętrzna.
2. Zasoby z cudzego CDN.
3. Brak escapowania, sanityzacji lub nonce.
4. Zbyt ogólne prefiksy funkcji i klas.
5. Cudza marka na początku slugu albo zgłoszenie z prywatnego adresu e-mail (Gmail) dla wtyczki firmowej.
6. `Stable tag` niezgodny z wersją w nagłówku.
7. Własny updater.
8. Pliki deweloperskie w zipie.
9. Bezpośrednie wywołania cURL.

## 12. Zgłoszenie krok po kroku

1. Zbuduj zip z tagu wydania (robi to workflow `release`): folder `asapchat/` z plikami wtyczki.
2. Zainstaluj zip na czystym WordPressie i uruchom Plugin Check. Zero błędów.
3. Zaloguj się na konto wydawcy (patrz `docs/accounts.md`) i wgraj zip: https://wordpress.org/plugins/developers/add/. W polu opisu poproś o slug `asapchat`.
4. Jedno konto może mieć w kolejce tylko jedną wtyczkę naraz.
5. Odpowiadaj na maile zespołu review z tego samego adresu. Poprawki wgrywasz jako nowy zip w tym samym zgłoszeniu. Zgłoszenie bez odpowiedzi przez 3 miesiące jest odrzucane.
6. Po akceptacji dostajesz repozytorium SVN. Publikacja:
   ```
   svn co https://plugins.svn.wordpress.org/asapchat
   # pliki wtyczki do trunk/, grafiki do assets/
   svn add trunk/* assets/*
   svn cp trunk tags/1.0.0
   svn ci -m "Release 1.0.0" --username LOGIN_WPORG
   ```
   Hasło SVN to osobne hasło z profilu WordPress.org, nie hasło do logowania. Jeśli obrazki pobierają się zamiast wyświetlać: `svn propset svn:mime-type image/png assets/*.png`.
7. Kolejne wydania robi GitHub Action `10up/action-wordpress-plugin-deploy` po wypchnięciu tagu `v*` (sekrety `SVN_USERNAME`, `SVN_PASSWORD` w środowisku `release`, które zatwierdza właściciel).
8. Po publikacji: dodaj właściciela jako committera (`docs/accounts.md`), uzupełnij tłumaczenie PL na translate.wordpress.org.

## 13. Czas review

Oficjalnie pierwsza odpowiedź do 4 tygodni, zespół celuje w mniej niż tydzień. Dane o kolejce z 2026 roku są sprzeczne (od „prawie zero” do ponad 5000 zgłoszeń). Planuj 1 do 4 tygodni lub więcej (do potwierdzenia). Każda runda poprawek to kolejne dni, dlatego zgłaszamy raz, czysto.

## 14. Opcjonalnie później: Woo Marketplace (woocommerce.com)

Poza zakresem v1. Wymaga konta sprzedawcy, testów QIT i ścieżki monetyzacji przez Woo (darmowy łącznik do usługi płatnej poza Woo wymaga osobnej umowy partnerskiej). Podział przychodu 70/30 (do potwierdzenia).
