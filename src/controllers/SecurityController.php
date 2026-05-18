<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    public function login() {
        // TODO sprawdzenie, czy użytkownik istnieje

        $this->render("login");
    }

    public function register() {
        $this->render("register");
    }
}
