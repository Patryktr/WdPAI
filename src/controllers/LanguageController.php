<?php

require_once 'AppController.php';
require_once __DIR__.'/../services/Translator.php';

class LanguageController extends AppController
{
    #[AllowedMethods('POST')]
    public function set(): void
    {
        $isLoggedIn = !empty($_SESSION['user_id']);
        $layout = $isLoggedIn ? 'app' : 'auth';

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->handleInvalidCsrfToken($layout);
            return;
        }

        $locale = (string) ($_POST['locale'] ?? '');

        if (Translator::isSupported($locale)) {
            Translator::setLocale($locale);
            $this->storeLocaleCookie(Translator::getLocale());
        }

        $this->redirect($this->resolveRedirectPath($isLoggedIn));
    }

    private function resolveRedirectPath(bool $isLoggedIn): string
    {
        $defaultPath = $isLoggedIn ? '/dashboard' : '/login';
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');

        if ($referer === '') {
            return $defaultPath;
        }

        $refererPath = parse_url($referer, PHP_URL_PATH);

        if (!is_string($refererPath) || $refererPath === '' || $refererPath[0] !== '/') {
            return $defaultPath;
        }

        if ($refererPath === '/language') {
            return $defaultPath;
        }

        return $refererPath;
    }

    private function storeLocaleCookie(string $locale): void
    {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

        setcookie('locale', $locale, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
