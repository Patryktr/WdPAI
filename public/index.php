<?php

$isProduction = (getenv('APP_ENV') ?: 'local') === 'production';
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('display_startup_errors', $isProduction ? '0' : '1');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false,
    ]);

    session_start();
}

require_once __DIR__.'/../Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

try {
    Routing::run($path);
} catch (Throwable $exception) {
    error_log('Unhandled application error: '.$exception->getMessage());

    AppController::renderErrorResponse(
        500,
        'Application Error',
        'Wystąpił błąd aplikacji. Spróbuj ponownie później.'
    );
}
