<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

class SecurityController extends AppController {
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_LOCK_SECONDS = 60;

    #[AllowedMethods('GET', 'POST')]
    public function login() {
        if ($this->isGet()) {
            $this->render("login", ["_layout" => "auth", "title" => "Login"]);
            return;
        }

        if (!$this->isPost()) {
            $this->render("login", ["_layout" => "auth", "title" => "Login"]);
            return;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->handleInvalidCsrfToken('auth');
            return;
        }

        $loginError = 'Email lub hasło jest niepoprawne.';
        $lockoutMessage = 'Zbyt wiele nieudanych prób. Spróbuj ponownie za chwilę.';

        if ($this->isLoginLocked()) {
            $this->renderLoginWithMessage($lockoutMessage);
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
            $this->registerFailedLoginAttempt($email);
            $this->renderLoginWithMessage($this->isLoginLocked() ? $lockoutMessage : $loginError);
            return;
        }

        $usersRepository = new UsersRepository();

        try {
            $user = $usersRepository->getUserByEmail($email);
        } catch (PDOException $exception) {
            $this->renderLoginWithMessage($loginError);
            return;
        }

        if (
            $user === null ||
            !$user->isActive() ||
            !password_verify($password, $user->getPassword() ?? '')
        ) {
            $this->registerFailedLoginAttempt($email);
            $this->renderLoginWithMessage($this->isLoginLocked() ? $lockoutMessage : $loginError);
            return;
        }

        session_regenerate_id(true);
        $this->clearLoginAttempts();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['is_logged_in'] = true;

        $this->redirect('/dashboard');
    }

    #[AllowedMethods('GET', 'POST')]
    public function register() {
        if ($this->isGet()) {
            $this->render("register", ["_layout" => "auth", "title" => "Register"]);
            return;
        }

        if (!$this->isPost()) {
            $this->render("register", ["_layout" => "auth", "title" => "Register"]);
            return;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->handleInvalidCsrfToken('auth');
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

    private function isLoginLocked(): bool
    {
        $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);

        if ($lockedUntil > time()) {
            return true;
        }

        if ($lockedUntil > 0) {
            $this->clearLoginAttempts();
        }

        return false;
    }

    private function registerFailedLoginAttempt(string $email): void
    {
        $_SESSION['login_failed_attempts'] = (int) ($_SESSION['login_failed_attempts'] ?? 0) + 1;

        $this->logFailedLoginAttempt($email);

        if ($_SESSION['login_failed_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['login_locked_until'] = time() + self::LOGIN_LOCK_SECONDS;
        }
    }

    private function clearLoginAttempts(): void
    {
        unset($_SESSION['login_failed_attempts']);
        unset($_SESSION['login_locked_until']);
    }

    private function logFailedLoginAttempt(string $email): void
    {
        $safeEmail = str_replace(["\r", "\n"], '', substr($email, 0, 255));
        $safeIp = str_replace(["\r", "\n"], '', substr($_SERVER['REMOTE_ADDR'] ?? 'unknown', 0, 45));

        error_log('Failed login attempt: '.json_encode([
            'email' => $safeEmail,
            'ip' => $safeIp,
            'timestamp' => date('c'),
        ]));
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

    #[AllowedMethods('GET')]
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
