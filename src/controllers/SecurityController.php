<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

class SecurityController extends AppController {

    public function login() {
        if ($this->isGet()) {
            $this->render("login", ["_layout" => "auth", "title" => "Login"]);
            return;
        }

        if (!$this->isPost()) {
            $this->render("login", ["_layout" => "auth", "title" => "Login"]);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($email === '') {
            $errors[] = 'Podaj email i hasło, aby się zalogować.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Sprawdź poprawność danych logowania.';
        }

        if ($password === '') {
            $errors[] = 'Podaj email i hasło, aby się zalogować.';
        }

        if (!empty($errors)) {
            $this->render("login", [
                "_layout" => "auth",
                "title" => "Login",
                "messages" => implode('<br>', array_unique($errors)),
            ]);
            return;
        }

        $usersRepository = new UsersRepository();
        $loginError = 'Email lub hasło jest niepoprawne';

        try {
            $user = $usersRepository->getUserByEmail($email);
        } catch (PDOException $exception) {
            $this->renderLoginWithMessage($loginError);
            return;
        }

        if (
            $user === null ||
            !$this->isUserActive($user) ||
            !password_verify($password, $user['password'])
        ) {
            $this->renderLoginWithMessage($loginError);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_logged_in'] = true;

        $this->redirect('/dashboard');
    }

    public function register() {
        if ($this->isGet()) {
            $this->render("register", ["_layout" => "auth", "title" => "Register"]);
            return;
        }

        if (!$this->isPost()) {
            $this->render("register", ["_layout" => "auth", "title" => "Register"]);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $errors = [];

        if ($username === '') {
            $errors[] = 'Nazwa użytkownika jest wymagana.';
        } elseif ($this->stringLength($username) > 50) {
            $errors[] = 'Nazwa użytkownika może mieć maksymalnie 50 znaków.';
        }

        if ($fullName === '') {
            $errors[] = 'Imię i nazwisko jest wymagane.';
        } elseif ($this->stringLength($fullName) > 100) {
            $errors[] = 'Imię i nazwisko może mieć maksymalnie 100 znaków.';
        }

        if ($email === '') {
            $errors[] = 'Email jest wymagany.';
        } elseif ($this->stringLength($email) > 100) {
            $errors[] = 'Email może mieć maksymalnie 100 znaków.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email ma niepoprawny format.';
        }

        if ($password === '') {
            $errors[] = 'Hasło jest wymagane.';
        } elseif ($this->stringLength($password) < 8) {
            $errors[] = 'Hasło musi mieć minimum 8 znaków.';
        }

        if ($password2 !== $password) {
            $errors[] = 'Powtórzone hasło musi być takie samo jak hasło.';
        }

        if (!empty($errors)) {
            $this->renderRegisterWithMessage(implode('<br>', $errors), $username, $fullName, $email);
            return;
        }

        $usersRepository = new UsersRepository();

        try {
            if ($usersRepository->getUserByEmail($email) !== null) {
                $this->renderRegisterWithMessage('Konto z podanym adresem email już istnieje.', $username, $fullName, $email);
                return;
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            if ($passwordHash === false) {
                $this->renderRegisterWithMessage('Nie udało się zabezpieczyć hasła. Spróbuj ponownie za chwilę.', $username, $fullName, $email);
                return;
            }

            $usersRepository->createUser($username, $email, $passwordHash, $fullName);
        } catch (PDOException $exception) {
            $this->renderRegisterWithMessage('Nie udało się utworzyć konta. Spróbuj ponownie za chwilę.', $username, $fullName, $email);
            return;
        }

        $this->redirect('/login');
    }

    private function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }

    private function renderRegisterWithMessage(string $message, string $username, string $fullName, string $email): void
    {
        $this->render("register", [
            "_layout" => "auth",
            "title" => "Register",
            "messages" => $message,
            "form" => [
                "username" => $username,
                "full_name" => $fullName,
                "email" => $email,
            ],
        ]);
    }

    private function renderLoginWithMessage(string $message): void
    {
        $this->render("login", [
            "_layout" => "auth",
            "title" => "Login",
            "messages" => $message,
        ]);
    }

    private function isUserActive(array $user): bool
    {
        return filter_var($user['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
        $this->redirect('/login');
    }
}
