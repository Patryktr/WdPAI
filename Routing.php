<?php

require_once __DIR__.'/src/controllers/SecurityController.php';
require_once __DIR__.'/src/controllers/DashboardController.php';

// TODO musimy zapewnić, że utworzony
// obiekt kontrolera ma tylko jedną instancję - SINGLETON

// TODO 2 /dashboard -- wszystkie dane
// /dashboard/12234 -- wyciągnie nam jakiś element o wskazanym ID 12234
// REGEX
class Routing {

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "index"
        ],
        "" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ],
    ];

    public static function run(string $path) {
        // TODO sprawdzać za pomocą array_key_exists
        switch($path) {
            case 'dashboard':
            case '':
            case 'login':
            case 'register':
                $controller = Routing::$routes[$path]["controller"];
                $action = Routing::$routes[$path]["action"];

                $controllerObj = new $controller;
                $id = null;

                $controllerObj->$action($id);
                break; 
            default:
                include 'public/views/404.html';
                break;
        }
    }
}
