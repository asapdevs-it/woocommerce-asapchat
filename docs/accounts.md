# Konto wydawcy na WordPress.org (AsapCommerce)

Konto zakłada deweloper. Właścicielem wtyczki jest firma AsapCommerce, a Kamil (GitHub `prz10`) ma mieć pełny dostęp od pierwszego dnia. Ten dokument prowadzi krok po kroku.

## Zanim zaczniesz

- **Adres e-mail.** Zespół WordPress.org rozpoznaje oficjalne wtyczki firm głównie po adresie e-mail w domenie firmy. Zgłoszenie wtyczki „AsapChat” z adresu Gmail zwykle kończy się pytaniem o prawo do marki i opóźnieniem. Dlatego:
  1. Poproś Kamila o adres w domenie firmy albo alias przekierowany do Ciebie i do Kamila (jak i kiedy: `ONBOARDING-DEV.md`, punkt 7). To jest wariant zalecany.
  2. Jeśli zakładasz konto na prywatnym adresie, przed zgłoszeniem zmień e-mail konta na firmowy (Profil > Account) albo przygotuj się na pytanie reviewera i odpowiedz, wskazując stronę asapchat.io i osobę kontaktową z firmy.
- **Jedno konto = jedna wtyczka w kolejce.** Nie zgłaszaj z tego konta niczego innego w trakcie review.
- **Hasła.** Login, hasło, kody zapasowe 2FA i hasło SVN zapisz w firmowym menedżerze haseł, we wpisie udostępnionym Kamilowi (dostęp do menedżera: `ONBOARDING-DEV.md`, punkt 7). Nigdy w repo, w issue ani na czacie.

## Kroki

1. Wejdź na https://login.wordpress.org/register i załóż konto.
   - Username: `asapcommerce` (to będzie widoczny „Contributor” i login do SVN; nie da się go zmienić). Gdy jest zajęty, nie wymyślaj innego sam: ustal go z Kamilem przed rejestracją.
   - E-mail: firmowy (patrz wyżej).
2. Potwierdź e-mail linkiem z wiadomości.
3. Włącz 2FA (uwierzytelnianie dwuskładnikowe, obowiązkowe dla właścicieli i committerów wtyczek): https://profiles.wordpress.org/me/profile/edit/group/3/?screen=two-factor . Użyj aplikacji typu Authenticator. Zapisz kody zapasowe w menedżerze haseł.
4. Wygeneruj hasło SVN: Profil > Account & Security > SVN credentials. To osobne hasło, używane tylko do `svn ci` i w GitHub Actions.
5. Uzupełnij profil: nazwa wyświetlana „AsapCommerce”, strona https://asapchat.io.
6. Zgłoś wtyczkę według `docs/marketplace-listing.md`, sekcja 12.

## Dostęp dla Kamila

1. Kamil zakłada własne konto WordPress.org z 2FA (osobne, nie współdzielone).
2. Po akceptacji wtyczki wejdź na `https://wordpress.org/plugins/asapchat/advanced/` i w sekcji **Committers** dodaj login Kamila. Committer może publikować wersje.
3. W sekcji **Support Reps** możesz dodać osoby, które tylko odpowiadają na forum wsparcia.
4. Do czasu akceptacji Kamil ma dostęp przez wpis w menedżerze haseł.

## Gdyby konto okazało się „złe”

Jeśli wtyczka została zgłoszona z niewłaściwego konta, nie zgłaszaj jej ponownie. Odpowiedz na maila z review i poproś o przeniesienie własności na właściwe konto.

## Sekrety w GitHubie

Środowisko `release` (Settings > Environments) zakłada właściciel repo i ustawia w nim `SVN_USERNAME` i `SVN_PASSWORD` (poproś o to w zadaniu 14, `ONBOARDING-DEV.md`, punkt 7). Deweloper nie potrzebuje do nich wglądu: wydanie uruchamia tag `v*`, a zatwierdza je właściciel.

## Checklista

- [ ] Konto `asapcommerce` na adresie firmowym
- [ ] 2FA włączone, kody zapasowe w menedżerze haseł
- [ ] Hasło SVN wygenerowane i zapisane
- [ ] Wpis w menedżerze haseł udostępniony Kamilowi
- [ ] Po akceptacji: Kamil dodany jako committer
- [ ] Sekrety `SVN_USERNAME` / `SVN_PASSWORD` w środowisku `release`
