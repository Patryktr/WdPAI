<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/CategoriesRepository.php';

class CategoriesController extends AppController {

    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->currentUserId();
        $categoriesRepository = new CategoriesRepository();

        $categoriesRepository->ensureDefaultCategoriesForUser($userId);
        $errors = [];
        $form = [
            "name" => '',
            "icon" => '',
            "color" => '#25ff16',
        ];

        if ($this->isPost()) {
            $form = [
                "name" => trim($_POST['name'] ?? ''),
                "icon" => trim($_POST['icon'] ?? ''),
                "color" => trim($_POST['color'] ?? ''),
            ];

            $errors = $this->validateCategory($form);

            if (empty($errors)) {
                try {
                    $categoriesRepository->createCategory(
                        $userId,
                        $form['name'],
                        $form['icon'] !== '' ? $form['icon'] : null,
                        $form['color'] !== '' ? $form['color'] : null
                    );
                    $this->setFlash('success', 'Kategoria została dodana.');
                    $this->redirect('/categories');
                } catch (PDOException $exception) {
                    $errors[] = 'Nie udało się dodać kategorii. Sprawdź, czy taka nazwa już nie istnieje.';
                }
            }
        }

        $this->render("categories", [
            "title" => "Categories",
            "categories" => $categoriesRepository->getCategoryStatsByUserId($userId),
            "errors" => $errors,
            "form" => $form,
        ]);
    }

    private function validateCategory(array $category): array
    {
        $errors = [];
        $nameLength = $this->stringLength($category['name']);
        $iconLength = $this->stringLength($category['icon']);
        $colorLength = $this->stringLength($category['color']);

        if ($category['name'] === '') {
            $errors[] = 'Nazwa kategorii jest wymagana.';
        } elseif ($nameLength < 2 || $nameLength > 50) {
            $errors[] = 'Nazwa kategorii musi mieć od 2 do 50 znaków.';
        }

        if ($iconLength > 30) {
            $errors[] = 'Ikona może mieć maksymalnie 30 znaków.';
        }

        if ($colorLength > 20) {
            $errors[] = 'Kolor może mieć maksymalnie 20 znaków.';
        }

        return $errors;
    }

    private function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
