# Konto wydawcy na WordPress.org (AsapCommerce)

Konto zakłada deweloper. Właścicielem wtyczki jest firma AsapCommerce, a Kamil (GitHub `prz10`) ma mieć pełny dostęp od pierwszego dnia. Ten dokument prowadzi krok po kroku.

## Zanim zaczniesz

- **Adres e-mail.** Konto zakładasz na swoim adresie e-mail, tym samym, którego używasz w pracy nad projektem. Jeśli reviewer WordPress.org zapyta o prawo do marki „AsapChat”, odpowiedz, wskazując stronę asapchat.io, wydawcę AsapCommerce i Kamila jako osobę kontaktową z firmy.
- **Jedno konto = jedna wtyczka w kolejce.** Nie zgłaszaj z tego konta niczego innego w trakcie review.
- **Hasła.** Login, hasło, kody zapasowe 2FA i hasło SVN przekaż Kamilowi bezpośrednio, w sposób, który z nim ustalisz. Nigdy w repo, w issue ani na publicznym czacie.

## Kroki

1. Wejdź na https://login.wordpress.org/register i załóż konto.
   - Username: `asapcommerce` (to będzie widoczny „Contributor” i login do SVN; nie da się go zmienić). Gdy jest zajęty, nie wymyślaj innego sam: ustal go z Kamilem przed rejestracją.
   - E-mail: Twój (patrz wyżej).
2. Potwierdź e-mail linkiem z wiadomości.
3. Włącz 2FA (uwierzytelnianie dwuskładnikowe, obowiązkowe dla właścicieli i committerów wtyczek): https://profiles.wordpress.org/me/profile/edit/group/3/?screen=two-factor . Użyj aplikacji typu Authenticator. Zapisz kody zapasowe i przekaż je Kamilowi razem z danymi logowania.
4. Wygeneruj hasło SVN: Profil > Account & Security > SVN credentials. To osobne hasło, używane tylko do `svn ci` i w GitHub Actions.
5. Uzupełnij profil: nazwa wyświetlana „AsapCommerce”, strona https://asapchat.io.
6. Zgłoś wtyczkę według `docs/marketplace-listing.md`, sekcja 12.

## Dostęp dla Kamila

1. Kamil zakłada własne konto WordPress.org z 2FA (osobne, nie współdzielone).
2. Po akceptacji wtyczki wejdź na `https://wordpress.org/plugins/asapchat/advanced/` i w sekcji **Committers** dodaj login Kamila. Committer może publikować wersje.
3. W sekcji **Support Reps** możesz dodać osoby, które tylko odpowiadają na forum wsparcia.
4. Do czasu akceptacji Kamil ma dostęp przez dane logowania, które mu przekazałeś.

## Gdyby konto okazało się „złe”

Jeśli wtyczka została zgłoszona z niewłaściwego konta, nie zgłaszaj jej ponownie. Odpowiedz na maila z review i poproś o przeniesienie własności na właściwe konto.

## Sekrety w GitHubie

Środowisko `release` (Settings > Environments) zakłada właściciel repo i ustawia w nim `SVN_USERNAME` i `SVN_PASSWORD` (poproś o to w zadaniu 14, `ONBOARDING-DEV.md`, punkt 7). Deweloper nie potrzebuje do nich wglądu: wydanie uruchamia tag `v*`, a zatwierdza je właściciel.

## Checklista

- [ ] Konto `asapcommerce` założone
- [ ] 2FA włączone, kody zapasowe zapisane
- [ ] Hasło SVN wygenerowane i zapisane
- [ ] Dane logowania przekazane Kamilowi
- [ ] Po akceptacji: Kamil dodany jako committer
- [ ] Sekrety `SVN_USERNAME` / `SVN_PASSWORD` w środowisku `release`
