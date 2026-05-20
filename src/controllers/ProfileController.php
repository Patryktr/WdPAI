<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

class ProfileController extends AppController {

    public function index(): void
    {
        $this->requireLogin();

        $usersRepository = new UsersRepository();
        $userId = $this->currentUserId();
        $user = $usersRepository->getUserById($userId);

        if ($user === null) {
            $this->setFlash('error', 'Nie znaleziono profilu użytkownika.');
            $this->redirect('/logout');
        }

        $passwordErrors = [];

        if ($this->isPost()) {
            $passwordErrors = $this->changePassword($usersRepository, $userId);
        }

        $this->render("profile", [
            "title" => "Profile",
            "user" => $user,
            "passwordErrors" => $passwordErrors,
        ]);
    }

    private function changePassword(UsersRepository $usersRepository, int $userId): array
    {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPassword2 = $_POST['new_password2'] ?? '';
        $errors = [];

        if ($currentPassword === '') {
            $errors[] = 'Aktualne hasło jest wymagane.';
        }

        if ($this->stringLength($newPassword) < 8) {
            $errors[] = 'Nowe hasło musi mieć minimum 8 znaków.';
        }

        if ($newPassword2 !== $newPassword) {
            $errors[] = 'Powtórzone nowe hasło musi być takie samo.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        $userWithPassword = $usersRepository->getUserWithPasswordById($userId);

        if ($userWithPassword === null || !password_verify($currentPassword, $userWithPassword['password'])) {
            return ['Aktualne hasło jest niepoprawne.'];
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        if ($passwordHash === false) {
            return ['Nie udało się zabezpieczyć nowego hasła. Spróbuj ponownie za chwilę.'];
        }

        $usersRepository->updatePassword($userId, $passwordHash);
        $this->setFlash('success', 'Hasło zostało zmienione.');
        $this->redirect('/profile');
    }

    private function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
