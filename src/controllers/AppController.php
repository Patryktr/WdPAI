<?php

class AppController {
    protected function render(string $view, array $params = []): void
    {
        $viewPath = __DIR__.'/../../public/views/'.$view.'.html';
        $notFoundPath = __DIR__.'/../../public/views/404.html';

        if (!file_exists($viewPath)) {
            $viewPath = $notFoundPath;
        }

        extract($params);
        ob_start();
        include $viewPath;
        echo ob_get_clean();
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
}
