# Project Status

## Aktualny stan projektu

Projekt jest prostą aplikacją PHP MVC uruchamianą w Dockerze. Obecnie zawiera podstawowy routing, kontrolery, repozytoria, widoki HTML/PHP, konfiguracje nginx/php-fpm/PostgreSQL oraz podstawowe style CSS.

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
|       |-- dashboard.html
|       |-- index.html
|       |-- login.html
|       |-- register.html
|       `-- partials
|           |-- head.html
|           `-- nav.html
`-- src
    |-- controllers
    |   |-- AppController.php
    |   |-- DashboardController.php
    |   `-- SecurityController.php
    `-- repositories
        |-- Repository.php
        `-- UsersRepository.php
```

## Główne elementy aplikacji

- `public/index.php` odbiera żądanie HTTP i przekazuje ścieżkę do `Routing::run()`.
- `Routing.php` definiuje aktualne trasy i wybiera odpowiedni kontroler oraz akcję.
- `src/controllers/AppController.php` zawiera wspólną logikę kontrolerów, m.in. `render()`, `isGet()` i `isPost()`.
- `src/controllers/SecurityController.php` renderuje widoki logowania i rejestracji.
- `src/controllers/DashboardController.php` pobiera użytkowników przez `UsersRepository` i renderuje widok `index`.
- `Database.php` tworzy połączenie PDO z PostgreSQL.
- `src/repositories/UsersRepository.php` zawiera metody dostępu do tabeli `users`.

## Aktualne ścieżki routingu

| Ścieżka | Kontroler | Akcja | Widok / efekt |
| --- | --- | --- | --- |
| `/` | `SecurityController` | `login` | `public/views/login.html` |
| `/login` | `SecurityController` | `login` | `public/views/login.html` |
| `/register` | `SecurityController` | `register` | `public/views/register.html` |
| `/dashboard` | `DashboardController` | `index` | `public/views/index.html` |
| inna ścieżka | brak | brak | `public/views/404.html` |

## Znane problemy

1. Niezgodność tabeli `users` z `UsersRepository`

   Naprawione: tabela `users` oraz `UsersRepository` uzywaja wspolnego modelu: `id`, `username`, `email`, `password`, `full_name`, `is_active`, `created_at`.

   `UsersRepository::createUser()` zapisuje teraz tylko kolumny obecne w schemacie tabeli.

2. Brak logiki logowania i rejestracji

   `SecurityController` aktualnie tylko renderuje formularze `login` i `register`. Nie ma jeszcze obsługi danych z formularzy, walidacji, sprawdzania użytkownika w bazie ani zapisu nowego konta.

3. Brak sesji

   Projekt nie zawiera jeszcze mechanizmu sesji. Nie ma `session_start()`, zapisu zalogowanego użytkownika do sesji ani ochrony tras wymagających logowania.

4. Problemy z kodowaniem polskich znaków

   W części plików było widać uszkodzone polskie znaki, np. `Twój email`, `hasło`, `zarejestruj się` oraz podobne znaki w komentarzach. Sugerowało to problem z kodowaniem plików lub sposobem ich odczytu/zapisu.

## Uwagi

Ten plik opisuje aktualny stan projektu. Nie wprowadza nowych funkcji i nie zmienia logiki aplikacji.
