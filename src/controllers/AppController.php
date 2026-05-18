<?php

class AppController {
    protected function render(string $view, array $params = []): void
    {
        $viewPath = __DIR__.'/../../public/views/'.$view.'.html';
        $notFoundPath = __DIR__.'/../../public/views/404.html';
        $layout = $params["_layout"] ?? "app";
        unset($params["_layout"]);

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
}
