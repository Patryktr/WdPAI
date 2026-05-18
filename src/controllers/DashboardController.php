<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

class DashboardController extends AppController {

    public function index() {
        // TODO pobieranie danych z bazy
        // wstawianie zmiennych do widoku
        $title = "Dashboard";
        $usersRepository = new UsersRepository();
        $users = $usersRepository->getUsers();

        $this->render("index", ["title" => $title, "users" => $users]);
    }
}
