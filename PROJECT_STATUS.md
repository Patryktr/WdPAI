# Project Status

## Aktualny stan projektu

Projekt jest prostą aplikacją PHP MVC uruchamianą w Dockerze. Obecnie zawiera routing oparty o mapę ścieżek, kontrolery, repozytoria, widoki HTML/PHP, wspólne layouty, konfiguracje nginx/php-fpm/PostgreSQL oraz podstawowe style CSS.

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
|       |-- expense-create.html
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
        |-- Repository.php
        `-- UsersRepository.php
```

## Główne elementy aplikacji

- `public/index.php` odbiera żądanie HTTP i przekazuje ścieżkę do `Routing::run()`.
- `Routing.php` mapuje ścieżki na kontrolery i akcje.
- `src/controllers/AppController.php` zawiera wspólną logikę kontrolerów, m.in. `render()`, `redirect()`, `isGet()` i `isPost()`.
- `AppController::render()` ładuje widok z `public/views`, przechwytuje jego treść do `$content`, a następnie osadza ją w layoucie.
- `public/views/layouts/app.php` jest wspólnym layoutem dla dashboardu i przyszłych stron aplikacji.
- `public/views/layouts/auth.php` jest uproszczonym layoutem dla logowania i rejestracji.
- `src/controllers/SecurityController.php` renderuje widoki logowania, rejestracji i placeholder wylogowania.
- `src/controllers/DashboardController.php` pobiera użytkowników przez `UsersRepository` i renderuje widok `index`.
- `Database.php` tworzy połączenie PDO z PostgreSQL.
- `src/repositories/UsersRepository.php` zawiera metody dostępu do tabeli `users`.

## Aktualne ścieżki routingu

| Ścieżka | Kontroler | Akcja | Widok / efekt |
| --- | --- | --- | --- |
| `/` | `SecurityController` | `login` | `public/views/login.html` |
| `/login` | `SecurityController` | `login` | `public/views/login.html` |
| `/register` | `SecurityController` | `register` | `public/views/register.html` |
| `/logout` | `SecurityController` | `logout` | `public/views/logout.html` |
| `/dashboard` | `DashboardController` | `index` | `public/views/index.html` |
| `/expenses` | `ExpensesController` | `index` | `public/views/expenses.html` |
| `/expenses/create` | `ExpensesController` | `create` | `public/views/expense-create.html` |
| `/categories` | `CategoriesController` | `index` | `public/views/categories.html` |
| `/statistics` | `StatisticsController` | `index` | `public/views/statistics.html` |
| `/profile` | `ProfileController` | `index` | `public/views/profile.html` |
| inna ścieżka | brak | brak | `public/views/404.html` |

## Widoki auth

- `public/views/login.html` zawiera formularz logowania w stylu dark fintech.
- Formularz logowania wysyła `POST` na `/login` i ma pola `email`, `password`.
- `public/views/register.html` zawiera formularz rejestracji w stylu dark fintech.
- Formularz rejestracji wysyła `POST` na `/register` i ma pola `username`, `full_name`, `email`, `password`, `password2`.
- Style widoków auth znajdują się w `public/styles/main.css` i są ograniczone klasą `auth-page`.

## Znane problemy

1. Brak logiki logowania i rejestracji

   `SecurityController` aktualnie renderuje formularze, ale nie obsługuje jeszcze danych z formularzy, walidacji, sprawdzania użytkownika w bazie ani zapisu nowego konta.

2. Brak sesji

   Projekt nie zawiera jeszcze mechanizmu sesji. Nie ma `session_start()`, zapisu zalogowanego użytkownika do sesji ani ochrony tras wymagających logowania.

3. Placeholdery nowych sekcji

   Trasy `/logout`, `/expenses`, `/expenses/create`, `/categories`, `/statistics` i `/profile` mają przygotowane kontrolery oraz widoki, ale nie zawierają jeszcze logiki biznesowej.

## Uwagi

Ten plik opisuje aktualny stan projektu. Nie wprowadza nowych funkcji i nie zmienia logiki aplikacji.
