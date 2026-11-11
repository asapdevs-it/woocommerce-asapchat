# WordPress.org: treść wpisu, grafiki, zgłoszenie

Wszystko, co trzeba wkleić lub wgrać, żeby wtyczka pojawiła się w katalogu WordPress.org. Wtyczka jest darmowa. Wymagania katalogu są w punkcie 10 jako lista do odhaczenia.

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

This plugin is the official connector. It links your site to your AsapChat account and adds the AsapChat scripts to your pages. No theme editing, no code to paste.

**How it works**

1. Install and activate the plugin.
2. Go to Settings > AsapChat and click "Connect to AsapChat".
3. Log in, or create a free account and finish the short setup wizard. You are sent back to WordPress automatically.
4. The chat appears on your site.

**What you can set in the plugin**

* **Widgets**: turn the chat, the product search and "add to cart from search" on and off with separate switches.
* **Look**: assistant name, main color, position (bottom right or bottom left) and two greeting messages. These are the same settings as in the AsapChat panel, so a change in one place shows up in the other.
* **Where to show**: delay the chat by 0 to 60 seconds and hide the widgets on selected pages, selected products or any address that contains a given text.
* **Status**: see whether the chat and the search have actually been seen on your site.
* **Orders and products**: see whether the assistant has access to your products and orders, and connect your WooCommerce store in one click from the AsapChat panel.

**What you need**

A free AsapChat account. The Free plan includes 50 AI conversations per month. The knowledge base, conversations and team inbox are managed in the AsapChat panel.

**Good to know**

* Works with or without WooCommerce.
* Compatible with WooCommerce High-Performance Order Storage (HPOS).
* The plugin does not contact AsapChat until you click Connect.
* The plugin does not read or send your orders or products. AsapChat reads them through the WooCommerce REST API, only after you approve it.
* The settings screen loads no remote fonts or assets.
* Removing the plugin removes all its data from your database.
```

## 5. Długi opis PL (do tłumaczenia na translate.wordpress.org po akceptacji)

```
AsapChat dodaje do Twojej strony na WordPressie dwie rzeczy:

* **Czat AI**, który odpowiada klientom na podstawie Twojej bazy wiedzy, produktów i statusów zamówień, a gdy trzeba, przekazuje rozmowę człowiekowi.
* **AsapSearch**, szybką wyszukiwarkę produktów dla sklepów WooCommerce, odporną na literówki.

Ta wtyczka to oficjalny łącznik. Łączy stronę z Twoim kontem AsapChat i dodaje skrypty AsapChat do stron. Bez edycji motywu i bez wklejania kodu.

**Jak to działa**

1. Zainstaluj i włącz wtyczkę.
2. Wejdź w Ustawienia > AsapChat i kliknij „Połącz z AsapChat”.
3. Zaloguj się albo załóż darmowe konto i przejdź krótki kreator. Do WordPressa wracasz automatycznie.
4. Czat pojawia się na stronie.

**Co ustawisz we wtyczce**

* **Widgety**: osobne przełączniki dla czatu, wyszukiwarki i dodawania do koszyka z wyszukiwarki.
* **Wygląd**: nazwa asystenta, kolor główny, pozycja (prawy lub lewy dół) i dwa powitania. To te same ustawienia co w panelu AsapChat. Zmiana w jednym miejscu jest widoczna w drugim.
* **Gdzie pokazywać**: opóźnienie czatu od 0 do 60 sekund oraz ukrycie widgetów na wybranych stronach, produktach albo adresach zawierających podany tekst.
* **Status**: widzisz, czy czat i wyszukiwarka były faktycznie widziane na Twojej stronie.
* **Zamówienia i produkty**: widzisz, czy asystent ma dostęp do produktów i zamówień. Sklep WooCommerce podłączasz jednym kliknięciem w panelu AsapChat.

**Czego potrzebujesz**

Darmowego konta AsapChat. Plan Free obejmuje 50 rozmów AI miesięcznie. Baza wiedzy, rozmowy i skrzynka zespołu są w panelu AsapChat.
```

## 6. FAQ (EN, do readme.txt)

1. **Do I need an AsapChat account?** Yes. You can create a free one during the connection step. After the setup wizard you are sent back to WordPress automatically.
2. **Is it free?** The plugin is free. AsapChat has a Free plan (50 AI conversations per month) and paid plans. See asapchat.io for current pricing.
3. **Does it work without WooCommerce?** Yes. The chat works on any WordPress site. The product search needs a store with imported products.
4. **Where do I change the chat name, color, position and greetings?** In Settings > AsapChat, section "Look", or in the AsapChat panel. Both places edit the same settings.
5. **Can I hide the chat on some pages?** Yes. In "Where to show" pick pages or products, or enter a part of the address (for example `/checkout`). The chat and the search are not added there.
6. **Can the chat appear a few seconds after the page loads?** Yes. Set the delay from 0 to 60 seconds in "Where to show".
7. **How does the assistant know my products and order statuses?** Click "Connect in AsapChat" in the "Orders and products" section. In the AsapChat panel choose "Connect with WooCommerce" and approve the WooCommerce authorization screen. You do not copy any keys.
8. **The one-click connection did not work. How do I create API keys manually?** Go to WooCommerce > Settings > Advanced > REST API, click "Add key", choose "Read" permissions, generate the key and paste the Consumer key and Consumer secret in the AsapChat panel.
9. **Why is the search switch inactive?** AsapChat has not imported your products yet. Connect your store in the "Orders and products" section. The search turns on by itself after the import.
10. **What does "Add to cart from search" do?** It shows an add to cart button in the search results. Turn it off if you want customers to open the product page first.
11. **The chat does not show up. What should I check?** Check that the Chat switch is on, that the page is not on the exclusion list, and clear your cache plugin. The "Status" section tells you whether the chat has been seen on your site.
12. **I use a cache or optimization plugin.** The scripts are marked to be skipped by JS optimization. If your plugin still combines them, exclude the AsapChat scripts from JS optimization.
13. **What happens when I disconnect or delete the plugin?** The scripts are removed from your pages immediately. Deleting the plugin removes its options. Your AsapChat account and data stay intact.
14. **How is the plugin updated?** Through WordPress.org, like any other plugin from the directory.

## 7. Sekcja „External services” (obowiązkowa, EN, do readme.txt)

Brak tej sekcji to jeden z najczęstszych powodów odrzucenia.

```
== External services ==

This plugin connects your site to AsapChat (https://asapchat.io), a service provided by AsapCommerce. It is required for the plugin to work: the chat and the product search are delivered by this service.

Nothing is sent before you click "Connect to AsapChat" or submit an install key.

1. Connecting (when you click Connect or submit an install key, and when you disconnect or delete the plugin):
   the plugin sends your site URL, a one-time code and a code verifier to https://panel.asapchat.io, and receives a connection token.
2. Configuration refresh (when you open the plugin settings page, and once a day):
   the plugin sends the connection token and your site URL to https://panel.asapchat.io/api/plugin/v1/config and receives the script addresses, their attributes, the look settings and the connection status.
3. Saving the look (when you click Save and a look field has changed):
   the plugin sends the connection token, your site URL and the changed values (assistant name, color, position, greetings) to https://panel.asapchat.io/api/plugin/v1/settings.
4. On your storefront (on every page where the widgets are enabled and not excluded):
   the visitor's browser loads the chat script and the search script from https://asapchat.io. These scripts communicate with https://panel.asapchat.io to deliver chat messages and search results. Messages typed by the visitor and search queries are sent to the service.

The plugin does not send your orders, products or WooCommerce API keys anywhere.

Terms of service: https://asapchat.io/regulamin
Privacy policy: https://asapchat.io/polityka-prywatnosci
```

Przed zgłoszeniem:

- [ ] Otwórz sklep z włączonymi widgetami i sprawdź w narzędziach deweloperskich przeglądarki (karta Sieć), z jakimi hostami rozmawiają widgety. Oczekiwane: `asapchat.io` (skrypty) i `panel.asapchat.io` (API). Każdy inny host dopisz do tekstu wyżej.
- [ ] Adresy regulaminu i polityki prywatności odpowiadają kodem 200 po przekierowaniu (`asapchat.io/...` przekierowuje na `panel.asapchat.io/...`).

## 8. Szkic `readme.txt`

Plik musi nazywać się dokładnie `readme.txt` (małe litery). Walidator: https://wordpress.org/plugins/developers/readme-validator/

```
=== AsapChat: AI Chat and Product Search ===
Contributors: asapcommerce
Tags: live chat, ai chatbot, product search, woocommerce, customer support
Requires at least: X.Y
Tested up to: X.Y
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
5. Optional: set the look, the delay and the excluded pages, and connect your orders and products.

== Frequently Asked Questions ==

(sekcja 6, każde pytanie jako "= Pytanie =")

== External services ==

(sekcja 7)

== Screenshots ==

1. Not connected: one button to connect your site.
2. Connected: shop name, plan, status and switches for chat, search and add to cart.
3. Look and "Where to show": name, color, position, greetings, delay and exclusions.
4. Orders and products: connection status and WooCommerce API key instructions.
5. The AsapChat widget on a storefront.
6. AsapSearch results on a WooCommerce store.

== Changelog ==

= 1.0.0 =
* First release.
```

Na końcu `readme.txt` dopisz też informację o foncie: `Open Sans, SIL Open Font License 1.1, bundled in admin/fonts/`.

Uwagi: `X.Y` w szkicu to miejsca do wypełnienia, nie kopiuj ich dosłownie. Zadanie 01 wpisuje w `readme.txt` prawdziwe wartości (`Requires at least` z nagłówka wtyczki, `Tested up to` = aktualna stabilna wersja WordPressa w dniu PR), a zadanie 14 aktualizuje je w dniu wydania. `Contributors` to login konta WordPress.org. `Tested up to` to tylko numer wersji głównej, aktualny w dniu wydania. `Requires at least` ustawiamy na „najnowsza minus 2” w dniu wydania. `Stable tag` nigdy `trunk`.

## 9. Grafiki (katalog `.wordpress-org/` w repo, w SVN trafiają do `/assets/`, nie do `trunk`)

| Plik | Rozmiar | Limit | Uwagi |
|---|---|---|---|
| `banner-772x250.png` | 772 x 250 px | 4 MB | logo AsapChat + hasło, fiolet #6543f0 |
| `banner-1544x500.png` | 1544 x 500 px | 4 MB | wersja dla ekranów o wysokiej gęstości |
| `icon-128x128.png` | 128 x 128 px | 1 MB | sam znak, bez tekstu |
| `icon-256x256.png` | 256 x 256 px | 1 MB | |
| `screenshot-1.png` | np. 1280 x 800 | 10 MB | stan „Niepołączony” |
| `screenshot-2.png` | np. 1280 x 800 | 10 MB | stan „Połączony”: nagłówek, status, widgety |
| `screenshot-3.png` | np. 1280 x 800 | 10 MB | sekcje „Wygląd” i „Gdzie pokazywać” |
| `screenshot-4.png` | np. 1280 x 800 | 10 MB | sekcja „Źródło zamówień i produktów” |
| `screenshot-5.png` | np. 1280 x 800 | 10 MB | czat na Twoim sklepie testowym (dane przykładowe WooCommerce, motyw Storefront) |
| `screenshot-6.png` | np. 1280 x 800 | 10 MB | wyniki wyszukiwarki na Twoim sklepie testowym wystawionym pod `https` (specyfikacja, punkt 21.4) |

Numer w nazwie pliku odpowiada numerowi linii w sekcji `== Screenshots ==`. Nazwy plików małymi literami. Na zrzutach żadnych danych prawdziwych klientów. Logo: pliki SVG w repo, `admin/img/asapchat-logo-white.svg` (na ciemne tło) i `admin/img/asapchat-logo-dark.svg` (na jasne). Bannery i ikony budujesz z nich sam w dowolnym edytorze grafiki: banner to logo i hasło z krótkiego opisu na tle `#161616` albo `#6543f0`, ikona to sam znak (pierwszy element logo, bez napisu) na tle `#6543f0`. Gotowych plików PNG nie ma.

## 10. Wymagania platformy (checklista przed zgłoszeniem)

- [ ] Cały kod i grafiki na licencji zgodnej z GPL.
- [ ] Kod czytelny, bez zaciemniania. Wtyczka nie zawiera zminifikowanego JS.
- [ ] Nic we wtyczce nie jest blokowane po okresie próbnym (limity planu egzekwuje usługa, nie wtyczka).
- [ ] Usługa zewnętrzna opisana w readme (sekcja 7).
- [ ] Zero żądań do zewnętrznych serwerów przed zgodą (kliknięcie „Połącz”).
- [ ] Skrypty widgetów ładowane tylko z adresów własnej usługi, żadnych cudzych CDN.
- [ ] Panel admina nie ładuje żadnych zdalnych zasobów: font Open Sans i logo są w paczce.
- [ ] Licencja fontu (SIL OFL 1.1, zgodna z GPL) wpisana w `readme.txt`.
- [ ] CSS panelu z prefiksem `asapchat-`, ładowany tylko na stronie wtyczki.
- [ ] Brak iframe w panelu admina, brak własnego mechanizmu aktualizacji i własnych komunikatów o nowej wersji.
- [ ] Wtyczka nie tworzy kluczy API WooCommerce i nie wysyła zamówień ani produktów.
- [ ] Komunikaty w panelu zamykane, tylko na stronie wtyczki.
- [ ] Maks. 5 tagów, bez nazw konkurencji.
- [ ] Prefiks `asapchat_` wszędzie, `ABSPATH` w każdym pliku.
- [ ] Sanityzacja wejścia, escapowanie wyjścia, nonce, sprawdzenie uprawnień.
- [ ] `wp_remote_*` zamiast cURL, brak składni HEREDOC.
- [ ] Plugin Check: zero błędów w kategorii „Plugin Repo”.
- [ ] Zip poniżej 1 MB (limit katalogu to 10 MB, nasz cel jest ostrzejszy), bez plików deweloperskich.
- [ ] Deklaracja HPOS, nagłówek `WC tested up to`.
- [ ] Konto zgłaszające ma 2FA.

## 11. Typowe powody odrzucenia

1. Nieopisana usługa zewnętrzna.
2. Zasoby z cudzego CDN, w tym fonty z Google Fonts w panelu admina.
3. Brak escapowania, sanityzacji lub nonce.
4. Zbyt ogólne prefiksy funkcji i klas.
5. Cudza marka na początku slugu. Reviewer może też zapytać o prawo do marki, gdy konto nie ma adresu w domenie firmy: konto zakładasz na swoim adresie, a na pytanie odpowiadasz według `docs/accounts.md` („Zanim zaczniesz”).
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

## 13. Czas review i ryzyko

Pierwsza odpowiedź zespołu review przychodzi zwykle w ciągu 1 do 4 tygodni. Każda runda poprawek to kolejne dni, dlatego zgłaszamy raz, czysto.

Ryzyko: zgłoszenie może zostać odrzucone albo wrócić z uwagami (np. o oznaczeniu marki w widgecie na froncie). Wtedy poprawiamy i wgrywamy nowy zip w tym samym zgłoszeniu. Nie wstrzymuje to prac nad kodem.
