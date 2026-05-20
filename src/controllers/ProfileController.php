<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

class ProfileController extends AppController {

    public function index(): void
    {
        $this->requireLogin();

        $usersRepository = new UsersRepository();
        $user = $usersRepository->getUserById($this->currentUserId());

        if ($user === null) {
            $this->setFlash('error', 'Nie znaleziono profilu użytkownika.');
            $this->redirect('/logout');
        }

        $this->render("profile", [
            "title" => "Profile",
            "user" => $user,
        ]);
    }
}
