<?php

require_once __DIR__.'/src/Attribute/AllowedMethods.php';
require_once __DIR__.'/src/Helpers/HttpMethodGuard.php';
require_once __DIR__.'/src/controllers/SecurityController.php';
require_once __DIR__.'/src/controllers/DashboardController.php';
require_once __DIR__.'/src/controllers/ExpensesController.php';
require_once __DIR__.'/src/controllers/CategoriesController.php';
require_once __DIR__.'/src/controllers/StatisticsController.php';
require_once __DIR__.'/src/controllers/ProfileController.php';
require_once __DIR__.'/src/controllers/LanguageController.php';

class Routing {

    private static array $routes = [
        "" => [
            "controller" => "SecurityController",
            "action" => "login",
        ],
        "login" => [
            "controller" => "SecurityController",
            "action" => "login",
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register",
        ],
        "logout" => [
            "controller" => "SecurityController",
            "action" => "logout",
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "index",
        ],
        "expenses" => [
            "controller" => "ExpensesController",
            "action" => "index",
        ],
        "expenses/create" => [
            "controller" => "ExpensesController",
            "action" => "create",
        ],
        "expenses/edit" => [
            "controller" => "ExpensesController",
            "action" => "edit",
        ],
        "expenses/delete" => [
            "controller" => "ExpensesController",
            "action" => "delete",
        ],
        "categories" => [
            "controller" => "CategoriesController",
            "action" => "index",
        ],
        "categories/edit" => [
            "controller" => "CategoriesController",
            "action" => "edit",
        ],
        "categories/delete" => [
            "controller" => "CategoriesController",
            "action" => "delete",
        ],
        "statistics" => [
            "controller" => "StatisticsController",
            "action" => "index",
        ],
        "profile" => [
            "controller" => "ProfileController",
            "action" => "index",
        ],
        "language" => [
            "controller" => "LanguageController",
            "action" => "set",
        ],
    ];

    public static function run(string $path): void
    {
        if (!array_key_exists($path, self::$routes)) {
            AppController::renderErrorResponse(
                404,
                __('error.404_title'),
                __('error.404_message'),
                'app'
            );
            return;
        }

        $controller = self::$routes[$path]["controller"];
        $action = self::$routes[$path]["action"];

        if (!checkRequestAllowed($controller, $action)) {
            self::renderMethodNotAllowed($controller, $action);
            return;
        }

        $controllerObject = new $controller();
        $controllerObject->$action();
    }

    private static function renderMethodNotAllowed(string $controller, string $action): void
    {
        $allowedMethods = getAllowedRequestMethods($controller, $action);
        if (!empty($allowedMethods)) {
            header('Allow: '.implode(', ', $allowedMethods));
        }

        AppController::renderErrorResponse(
            405,
            __('error.405_title'),
            __('error.405_message'),
            'app'
        );
    }
}
