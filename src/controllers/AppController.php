<?php

class AppController {
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function render(string $view, array $params = []): void
    {
        $params['flash'] = $params['flash'] ?? $this->getFlash();
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
