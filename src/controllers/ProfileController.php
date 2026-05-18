<?php

require_once 'AppController.php';

class ProfileController extends AppController {

    public function index(): void
    {
        $this->render("profile", ["title" => "Profile"]);
    }
}
