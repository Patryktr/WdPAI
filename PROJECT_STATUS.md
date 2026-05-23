# Project Status

## Aktualny stan projektu

Projekt jest prostą aplikacją PHP MVC uruchamianą w Dockerze. Obecnie zawiera routing oparty o mapę ścieżek, kontrolery, repozytoria, widoki HTML/PHP, wspólne layouty, konfiguracje nginx/php-fpm/PostgreSQL oraz podstawowe style CSS.

Aktualny etap prac obejmuje działający CRUD wydatków, obsługę rejestracji i logowania użytkownika, sesję PHP, wylogowanie, ochronę stron wymagających zalogowania, kontrolę dozwolonych metod HTTP przez atrybuty PHP 8 oraz panel finansowy dashboardu oparty o dane aktualnego użytkownika. Część sekcji aplikacji nadal pozostaje placeholderami do dalszej rozbudowy.

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
    |-- Attribute
    |   `-- AllowedMethods.php
    |-- controllers
    |   |-- AppController.php
    |   |-- CategoriesController.php
    |   |-- DashboardController.php
    |   |-- ExpensesController.php
    |   |-- ProfileController.php
    |   |-- SecurityController.php
    |   `-- StatisticsController.php
    |-- Helpers
    |   `-- HttpMethodGuard.php
    `-- repositories
        |-- CategoriesRepository.php
        |-- ExpensesRepository.php
        |-- Repository.php
        `-- UsersRepository.php
```

## Główne elementy aplikacji

- `public/index.php` uruchamia sesję PHP z bezpiecznymi parametrami cookie, odbiera żądanie HTTP i przekazuje ścieżkę do `Routing::run()`.
- `Routing.php` mapuje ścieżki na kontrolery i akcje oraz przed wywołaniem akcji sprawdza, czy aktualna metoda HTTP jest dozwolona.
- `src/Attribute/AllowedMethods.php` definiuje atrybut PHP 8 do oznaczania metod kontrolerów dozwolonymi metodami HTTP.
- `src/Helpers/HttpMethodGuard.php` używa `ReflectionMethod`, aby odczytać atrybut `AllowedMethods` i porównać go z `$_SERVER['REQUEST_METHOD']`.
- `src/controllers/AppController.php` zawiera wspólną logikę kontrolerów: `render()`, `redirect()`, `isGet()`, `isPost()`, `requireLogin()`, obsługę sesji i komunikaty flash.
- `AppController::render()` ładuje widok z `public/views`, przechwytuje jego treść do `$content`, a następnie osadza ją w wybranym layoucie.
- `public/views/layouts/app.php` jest wspólnym layoutem dla dashboardu i stron aplikacji. Zawiera dark fintech shell z lewym sidebarem, topbarem, kontenerem contentu i mobilną dolną nawigacją.
- `public/views/layouts/auth.php` jest layoutem dla logowania i rejestracji.
- `src/controllers/SecurityController.php` obsługuje logowanie, rejestrację, zapis użytkownika, weryfikację hasła, zapis danych użytkownika do sesji i wylogowanie.
- `src/controllers/ExpensesController.php` obsługuje listę, dodawanie, edycję i usuwanie wydatków oraz zapewnia domyślne kategorie dla aktualnego użytkownika.
- `src/controllers/DashboardController.php` renderuje panel finansowy z metrykami wydatków aktualnego użytkownika.
- `Database.php` tworzy połączenie PDO z PostgreSQL.

## Aktualne ścieżki routingu

| Ścieżka | Kontroler | Akcja | Metody HTTP | Widok / efekt |
| --- | --- | --- | --- | --- |
| `/` | `SecurityController` | `login` | `GET`, `POST` | `public/views/login.html` |
| `/login` | `SecurityController` | `login` | `GET`, `POST` | `public/views/login.html` |
| `/register` | `SecurityController` | `register` | `GET`, `POST` | `public/views/register.html` |
| `/logout` | `SecurityController` | `logout` | `GET` | wyczyszczenie sesji i redirect na `/login` |
| `/dashboard` | `DashboardController` | `index` | `GET` | panel finansowy użytkownika |
| `/expenses` | `ExpensesController` | `index` | `GET` | lista wydatków |
| `/expenses/create` | `ExpensesController` | `create` | `GET`, `POST` | `public/views/expense-form.html` |
| `/expenses/edit?id=...` | `ExpensesController` | `edit` | `GET`, `POST` | formularz edycji wydatku |
| `/expenses/delete` | `ExpensesController` | `delete` | `POST` | usunięcie wydatku |
| `/categories` | `CategoriesController` | `index` | `GET`, `POST` | `public/views/categories.html` |
| `/categories/edit?id=...` | `CategoriesController` | `edit` | `GET`, `POST` | formularz edycji kategorii |
| `/categories/delete` | `CategoriesController` | `delete` | `POST` | usunięcie kategorii |
| `/statistics` | `StatisticsController` | `index` | `GET` | `public/views/statistics.html` |
| `/profile` | `ProfileController` | `index` | `GET`, `POST` | `public/views/profile.html` |
| inna ścieżka | brak | brak | brak | `public/views/404.html` |

## Kontrola metod HTTP

Akcje kontrolerów są oznaczone atrybutem `#[AllowedMethods(...)]`. Router przed utworzeniem kontrolera sprawdza dozwolone metody przez helper `checkRequestAllowed()`, który korzysta z `ReflectionMethod`.

Jeśli żądanie używa niedozwolonej metody HTTP:

- aplikacja ustawia `http_response_code(405)`
- dodaje nagłówek `Allow` z listą dozwolonych metod
- pokazuje `public/views/error.html`
- nie wywołuje akcji kontrolera
- nie używa `die()`

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
- `getUserByEmail(string $email): ?User`
- `getUserById(int $id): ?User`
- `getUserWithPasswordById(int $id): ?User`
- `createUser(string $username, string $email, string $passwordHash, string $fullName): void`
- `updatePassword(int $id, string $passwordHash): void`

`CategoriesRepository`:

- `ensureDefaultCategoriesForUser(int $userId): void`
- `createCategory(int $userId, string $name, ?string $icon, ?string $color): void`
- `getCategoryById(int $id, int $userId): ?Category`
- `updateCategory(int $id, int $userId, string $name, ?string $icon, ?string $color): void`
- `deleteCategory(int $id, int $userId): void`
- `categoryHasExpenses(int $id, int $userId): bool`
- `getCategoriesByUserId(int $userId): array`
- `getCategoryStatsByUserId(int $userId): array`
- `categoryBelongsToUser(int $categoryId, int $userId): bool`

`ExpensesRepository`:

- `getRecentExpensesByUserId(int $userId, int $limit): array`
- `getMonthlyTotalByUserId(int $userId): float`
- `getMonthlyCountByUserId(int $userId): int`
- `getTotalByUserId(int $userId): float`
- `getBiggestExpenseByUserId(int $userId): ?array`
- `getCategorySummaryByUserId(int $userId): array`
- `getExpensesByUserId(int $userId, array $filters = []): array`
- `getExpenseById(int $id, int $userId): ?Expense`
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
- `public/views/categories.html` obsługuje listę kategorii, formularz dodawania oraz akcje edycji i usuwania.
- `public/views/category-form.html` obsługuje edycję kategorii.
- `public/views/statistics.html` pokazuje podsumowania i dane wykresów przygotowane w `StatisticsController`.
- `public/views/profile.html` pokazuje dane profilu i formularz zmiany hasła.

## Znane problemy / następne kroki

1. Brak testów automatycznych dla metod HTTP

   Nowy mechanizm `AllowedMethods` został sprawdzony ręcznie, ale warto dodać testy regresji dla tras `GET`/`POST` i odpowiedzi `405`.

2. Dashboard wymaga dalszego rozwoju

   `/dashboard` pokazuje już metryki wydatków użytkownika, ale wykres trendu jest nadal statycznym elementem UI.

3. Brak testów automatycznych dla auth

   Logowanie, rejestracja, sesja, CSRF i ochrona tras wymagają jeszcze pokrycia testami lub ręcznej checklisty regresji.

## Uwagi

Ten plik opisuje aktualny stan projektu. Nie wprowadza nowych funkcji i nie zmienia logiki aplikacji.
