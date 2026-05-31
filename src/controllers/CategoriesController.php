<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/CategoriesRepository.php';

class CategoriesController extends AppController {
    private CategoriesRepository $categoriesRepository;

    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->categoriesRepository = new CategoriesRepository();
        $this->categoriesRepository->ensureDefaultCategoriesForUser($this->currentUserId());
    }

    #[AllowedMethods('GET', 'POST')]
    public function index(): void
    {
        $userId = $this->currentUserId();
        $errors = [];
        $form = [
            "name" => '',
            "icon" => '',
            "color" => '#25ff16',
        ];

        if ($this->isPost()) {
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->handleInvalidCsrfToken();
                return;
            }

            $form = [
                "name" => trim($_POST['name'] ?? ''),
                "icon" => trim($_POST['icon'] ?? ''),
                "color" => trim($_POST['color'] ?? ''),
            ];

            $errors = $this->validateCategory($form);

            if (empty($errors)) {
                try {
                    $this->categoriesRepository->createCategory(
                        $userId,
                        $form['name'],
                        $form['icon'] !== '' ? $form['icon'] : null,
                        $form['color'] !== '' ? $form['color'] : null
                    );
                    $this->setFlash('success', __('flash.saved'));
                    $this->redirect('/categories');
                } catch (PDOException $exception) {
                    $errors[] = 'Nie udało się dodać kategorii. Sprawdź, czy taka nazwa już nie istnieje.';
                }
            }
        }

        $this->render("categories", [
            "title" => __('categories.title'),
            "categories" => $this->categoriesRepository->getCategoryStatsByUserId($userId),
            "errors" => $errors,
            "form" => $form,
        ]);
    }

    #[AllowedMethods('GET', 'POST')]
    public function edit(): void
    {
        $userId = $this->currentUserId();
        $id = (int) ($_GET['id'] ?? 0);
        $category = $this->categoriesRepository->getCategoryById($id, $userId);

        if ($category === null) {
            $this->setFlash('error', __('categories.no_categories'));
            $this->redirect('/categories');
        }

        $errors = [];
        $form = [
            'name' => $category->getName(),
            'icon' => (string) ($category->getIcon() ?? ''),
            'color' => (string) ($category->getColor() ?? '#25ff16'),
        ];

        if ($this->isPost()) {
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->handleInvalidCsrfToken();
                return;
            }

            $form = $this->categoryFromRequest();
            $errors = $this->validateCategory($form);

            if (empty($errors)) {
                try {
                    $this->categoriesRepository->updateCategory(
                        $id,
                        $userId,
                        $form['name'],
                        $form['icon'] !== '' ? $form['icon'] : null,
                        $form['color'] !== '' ? $form['color'] : null
                    );

                    $this->setFlash('success', __('flash.updated'));
                    $this->redirect('/categories');
                } catch (PDOException $exception) {
                    $errors[] = 'Nie udało się zaktualizować kategorii. Sprawdź, czy taka nazwa już nie istnieje.';
                }
            }
        }

        $this->render("category-form", [
            "title" => __('categories.edit'),
            "mode" => "edit",
            "category" => $category,
            "form" => $form,
            "errors" => $errors,
        ]);
    }

    #[AllowedMethods('POST')]
    public function delete(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/categories');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->handleInvalidCsrfToken();
            return;
        }

        $userId = $this->currentUserId();
        $id = (int) ($_POST['id'] ?? 0);
        $category = $this->categoriesRepository->getCategoryById($id, $userId);

        if ($id <= 0 || $category === null) {
            $this->setFlash('error', __('categories.no_categories'));
            $this->redirect('/categories');
        }

        if ($category->isDefault()) {
            $this->setFlash('error', 'Nie można usunąć kategorii bazowej.');
            $this->redirect('/categories');
        }

        if ($this->categoriesRepository->categoryHasExpenses($id, $userId)) {
            $this->setFlash('error', 'Nie można usunąć kategorii, która ma przypisane wydatki.');
            $this->redirect('/categories');
        }

        $this->categoriesRepository->deleteCategory($id, $userId);
        $this->setFlash('success', __('flash.deleted'));
        $this->redirect('/categories');
    }

    private function categoryFromRequest(): array
    {
        return [
            "name" => trim($_POST['name'] ?? ''),
            "icon" => trim($_POST['icon'] ?? ''),
            "color" => trim($_POST['color'] ?? ''),
        ];
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
