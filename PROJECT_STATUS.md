# Project Status

## Aktualny stan projektu

Projekt jest prostą aplikacją PHP MVC uruchamianą w Dockerze. Obecnie zawiera routing oparty o mapę ścieżek, kontrolery, repozytoria, widoki HTML/PHP, wspólne layouty, konfiguracje nginx/php-fpm/PostgreSQL oraz podstawowe style CSS.

Aktualny etap prac obejmuje działający CRUD wydatków, obsługę rejestracji i logowania użytkownika, sesję PHP, wylogowanie, ochronę stron wymagających zalogowania oraz panel finansowy dashboardu oparty o dane aktualnego użytkownika. Część sekcji aplikacji nadal pozostaje placeholderami do dalszej rozbudowy.

## Środowisko Docker

- nginx działa na porcie `8080`
- php-fpm używa PHP `8.3`
- PostgreSQL jest wystawiony lokalnie na porcie `5434`
- pgAdmin jest wystawiony lokalnie na porcie `5051`
- konfiguracja kontenerów znajduje się w `docker-compose.yaml`

## Struktura katalogów

```text
.
|-- .gitignore
|-- config.php
|-- Database.php
|-- docker-compose.yaml
|-- PROJECT_STATUS.md
|-- Routing.php
|-- docker
|   |-- db
|   |   |-- Dockerfile
|   |   `-- init.sql
|   |-- nginx
|   |   |-- Dockerfile
|   |   `-- nginx.conf
|   `-- php
|       `-- Dockerfile
|-- public
|   |-- index.php
|   |-- scripts
|   |   `-- main.js
|   |-- styles
|   |   `-- main.css
|   `-- views
|       |-- 404.html
|       |-- categories.html
|       |-- dashboard.html
|       |-- expense-form.html
|       |-- expenses.html
|       |-- index.html
|       |-- login.html
|       |-- logout.html
|       |-- profile.html
|       |-- register.html
|       |-- statistics.html
|       |-- layouts
|       |   |-- app.php
|       |   `-- auth.php
|       `-- partials
|           |-- head.html
|           `-- nav.html
`-- src
    |-- controllers
    |   |-- AppController.php
    |   |-- CategoriesController.php
    |   |-- DashboardController.php
    |   |-- ExpensesController.php
    |   |-- ProfileController.php
    |   |-- SecurityController.php
    |   `-- StatisticsController.php
    `-- repositories
        |-- CategoriesRepository.php
        |-- ExpensesRepository.php
        |-- Repository.php
        `-- UsersRepository.php
```

## Główne elementy aplikacji

- `public/index.php` uruchamia sesję PHP z bezpiecznymi parametrami cookie, odbiera żądanie HTTP i przekazuje ścieżkę do `Routing::run()`.
- `Routing.php` mapuje ścieżki na kontrolery i akcje.
- `src/controllers/AppController.php` zawiera wspólną logikę kontrolerów: `render()`, `redirect()`, `isGet()`, `isPost()`, `requireLogin()`, obsługę sesji i komunikaty flash.
- `AppController::render()` ładuje widok z `public/views`, przechwytuje jego treść do `$content`, a następnie osadza ją w wybranym layoucie.
- `public/views/layouts/app.php` jest wspólnym layoutem dla dashboardu i stron aplikacji. Zawiera dark fintech shell z lewym sidebarem, topbarem, kontenerem contentu i mobilną dolną nawigacją.
- `public/views/layouts/auth.php` jest layoutem dla logowania i rejestracji.
- `src/controllers/SecurityController.php` obsługuje logowanie, rejestrację, zapis użytkownika, weryfikację hasła, zapis danych użytkownika do sesji i wylogowanie.
- `src/controllers/ExpensesController.php` obsługuje listę, dodawanie, edycję i usuwanie wydatków oraz zapewnia domyślne kategorie dla aktualnego użytkownika.
- `src/controllers/DashboardController.php` renderuje panel finansowy z metrykami wydatków aktualnego użytkownika.
- `Database.php` tworzy połączenie PDO z PostgreSQL.

## Aktualne ścieżki routingu

| Ścieżka | Kontroler | Akcja | Widok / efekt |
| --- | --- | --- | --- |
| `/` | `SecurityController` | `login` | `public/views/login.html` |
| `/login` | `SecurityController` | `login` | `public/views/login.html` |
| `/register` | `SecurityController` | `register` | `public/views/register.html` |
| `/logout` | `SecurityController` | `logout` | wyczyszczenie sesji i redirect na `/login` |
| `/dashboard` | `DashboardController` | `index` | panel finansowy użytkownika |
| `/expenses` | `ExpensesController` | `index` | lista wydatków |
| `/expenses/create` | `ExpensesController` | `create` | `public/views/expense-form.html` |
| `/expenses/edit?id=...` | `ExpensesController` | `edit` | formularz edycji wydatku |
| `/expenses/delete` | `ExpensesController` | `delete` | usunięcie wydatku przez POST |
| `/categories` | `CategoriesController` | `index` | `public/views/categories.html` |
| `/statistics` | `StatisticsController` | `index` | `public/views/statistics.html` |
| `/profile` | `ProfileController` | `index` | `public/views/profile.html` |
| inna ścieżka | brak | brak | `public/views/404.html` |

## CRUD wydatków

Zaimplementowane funkcje:

- lista wydatków użytkownika
- dodanie wydatku
- edycja wydatku
- usunięcie wydatku
- komunikaty sukcesu/błędu przez flash
- redirect na `/expenses` po zapisie, edycji i usunięciu

Pola formularza wydatku:

- `name`
- `amount`
- `category_id`
- `expense_date`
- `note`

Walidacja:

- `amount` musi być liczbą większą od `0`
- `name` musi mieć od `3` do `100` znaków
- `note` może mieć maksymalnie `500` znaków
- `expense_date` musi być poprawną datą w formacie `YYYY-MM-DD`
- `category_id` musi należeć do aktualnego użytkownika

Widok `/expenses` zawiera:

- tabelę na desktopie
- karty wydatków na mobile
- wyszukiwarkę
- filtr kategorii
- filtr daty od/do
- formularz dodawania/edycji w stylu dark fintech inspirowany widokiem `New Expense`

CRUD wydatków używa `$_SESSION['user_id']` jako identyfikatora aktualnie zalogowanego użytkownika. Strony wydatków są chronione przez `requireLogin()`.

Domyślne kategorie są automatycznie tworzone dla użytkownika przy wejściu w sekcję wydatków, jeśli jeszcze ich nie ma:

- Food
- Transport
- Retail
- Fun
- Health
- Bills
- Travel
- Other

## Dashboard finansowy

`/dashboard` pokazuje panel w stylu dark fintech oparty o dane aktualnego użytkownika:

- suma wydatków w bieżącym miesiącu
- łączna suma wydatków
- liczba transakcji w bieżącym miesiącu
- największy wydatek
- największa kategoria
- ostatnie 5 wydatków
- podsumowanie kategorii
- przycisk `Dodaj wydatek`

Dane dashboardu są pobierane z `ExpensesRepository` wyłącznie dla `$_SESSION['user_id']`.

## Logowanie, rejestracja i sesja

Zaimplementowane funkcje:

- walidacja formularza rejestracji
- sprawdzanie, czy email jest już zajęty
- zapis nowego użytkownika przez `UsersRepository::createUser()`
- hashowanie hasła przez `password_hash(..., PASSWORD_BCRYPT)`
- walidacja formularza logowania
- pobranie użytkownika przez `UsersRepository::getUserByEmail()`
- sprawdzanie aktywności użytkownika przez `is_active`
- weryfikacja hasła przez `password_verify()`
- regeneracja ID sesji po poprawnym logowaniu
- zapis danych użytkownika do `$_SESSION`
- wylogowanie przez wyczyszczenie `$_SESSION`, usunięcie cookie sesyjnego i `session_destroy()`

Sesja startuje w `public/index.php` przed routingiem. Cookie sesyjne ma ustawione:

- `httponly: true`
- `samesite: Lax`
- `secure: false` dla lokalnego HTTP w Dockerze

Po poprawnym logowaniu ustawiane są klucze sesji:

- `user_id`
- `user_email`
- `username`
- `is_logged_in`

Hasło nie jest zapisywane w sesji ani przekazywane do widoku.

Strony chronione przez `requireLogin()`:

- `/dashboard`
- `/expenses`
- `/expenses/create`
- `/expenses/edit`
- `/expenses/delete`
- `/categories`
- `/statistics`
- `/profile`

## Baza danych

`docker/db/init.sql` tworzy obecnie:

- tabelę `users`
- tabelę `categories`
- tabelę `expenses`
- użytkownika demo
- podstawowe kategorie demo dla użytkownika `1`

W działającym lokalnym kontenerze PostgreSQL tabele `categories` i `expenses` zostały również utworzone ręcznie przez migrację SQL, ponieważ `init.sql` wykonuje się automatycznie tylko przy inicjalizacji pustej bazy.

## Repozytoria

`UsersRepository`:

- `getUsers(): ?array`
- `getUserByEmail(string $email): ?array`
- `getUserById(int $id): ?array`
- `createUser(string $username, string $email, string $passwordHash, string $fullName): void`

`CategoriesRepository`:

- `ensureDefaultCategoriesForUser(int $userId): void`
- `getCategoriesByUserId(int $userId): array`
- `categoryBelongsToUser(int $categoryId, int $userId): bool`

`ExpensesRepository`:

- `getRecentExpensesByUserId(int $userId, int $limit): array`
- `getMonthlyTotalByUserId(int $userId): float`
- `getMonthlyCountByUserId(int $userId): int`
- `getTotalByUserId(int $userId): float`
- `getBiggestExpenseByUserId(int $userId): ?array`
- `getCategorySummaryByUserId(int $userId): array`
- `getExpensesByUserId(int $userId, array $filters = []): array`
- `getExpenseById(int $id, int $userId): ?array`
- `createExpense(...)`
- `updateExpense(...)`
- `deleteExpense(int $id, int $userId): void`

Repozytoria używają zapytań przygotowanych PDO.

## Widoki i layouty

- `public/views/layouts/auth.php` ładuje `/styles/main.css?v=auth-dark-5` i ustawia `body` z klasą `auth-page`.
- `public/views/layouts/app.php` buduje layout po zalogowaniu: sidebar, topbar, kontener contentu i mobile bottom navigation.
- `public/views/login.html` ma widok w stylu dark fintech: jednolite ciemne tło, ikonę portfela, brand `Luminous Wealth`, pola logowania i neonowy przycisk.
- `public/views/register.html` jest utrzymany w tym samym stylu auth.
- `public/views/index.html` jest finansowym dashboardem osadzanym we wspólnym layoucie `app.php`.
- `public/views/expense-form.html` ma dark fintech formularz dodawania/edycji wydatku z kafelkami kategorii.
- Widoki `categories`, `statistics`, `profile` i `logout` są nadal placeholderami.

## Znane problemy / następne kroki

1. Kategorie są tylko częściowo obsłużone

   Domyślne kategorie są tworzone automatycznie dla użytkownika, ale CRUD kategorii nie jest jeszcze zaimplementowany.

2. Dashboard wymaga dalszego rozwoju

   `/dashboard` pokazuje już metryki wydatków użytkownika, ale wykres trendu jest nadal statycznym elementem UI.

3. Brak testów automatycznych dla auth

   Logowanie, rejestracja, sesja i ochrona tras wymagają jeszcze pokrycia testami lub ręcznej checklisty regresji.

## Uwagi

Ten plik opisuje aktualny stan projektu. Nie wprowadza nowych funkcji i nie zmienia logiki aplikacji.
