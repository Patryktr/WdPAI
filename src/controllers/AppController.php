<?php

class AppController {
    private const DEFAULT_ERROR_MESSAGE = 'Wystąpił błąd aplikacji. Spróbuj ponownie później.';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function render(string $view, array $params = []): void
    {
        $params['flash'] = $params['flash'] ?? $this->getFlash();
        $params['csrfToken'] = $params['csrfToken'] ?? $this->generateCsrfToken();
        $viewPath = __DIR__.'/../../public/views/'.$view.'.html';
        $notFoundPath = __DIR__.'/../../public/views/404.html';
        $layout = $params["_layout"] ?? "app";
        $bodyClass = $params["_body_class"] ?? "";
        unset($params["_layout"]);
        unset($params["_body_class"]);

        if (!file_exists($viewPath)) {
            $viewPath = $notFoundPath;
            $layout = null;
        }

        extract($params);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutPath = __DIR__.'/../../public/views/layouts/'.$layout.'.php';

        if (!file_exists($layoutPath)) {
            echo $content;
            return;
        }

        include $layoutPath;
    }

    public static function renderErrorResponse(
        int $statusCode = 500,
        string $title = 'Error',
        string $message = self::DEFAULT_ERROR_MESSAGE,
        ?string $layout = null
    ): void {
        http_response_code($statusCode);

        $viewPath = __DIR__.'/../../public/views/error.html';
        $layoutPath = $layout !== null ? __DIR__.'/../../public/views/layouts/'.$layout.'.php' : null;
        $bodyClass = '';

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($layoutPath === null || !file_exists($layoutPath)) {
            echo $content;
            return;
        }

        include $layoutPath;
    }

    protected function redirect(string $path): void
    {
        header("Location: ".$path);
        exit();
    }

    protected function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token): bool
    {
        if (!is_string($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    protected function handleInvalidCsrfToken(string $layout = 'app'): void
    {
        self::renderErrorResponse(
            403,
            'Forbidden',
            'Nieprawidlowy token CSRF. Odswiez strone i sprobuj ponownie.',
            $layout
        );
    }

    protected function currentUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 1);
    }

    protected function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    private function getFlash(): ?array
    {
        if (empty($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}
