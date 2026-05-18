<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    public function login() {
        // TODO sprawdzenie, czy użytkownik istnieje

        $this->render("login", ["_layout" => "auth", "title" => "Login"]);
    }

    public function register() {
        $this->render("register", ["_layout" => "auth", "title" => "Register"]);
    }

    public function logout(): void
    {
        $this->render("logout", ["title" => "Logout"]);
    }
}
