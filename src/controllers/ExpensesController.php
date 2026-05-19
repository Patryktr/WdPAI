<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/ExpensesRepository.php';
require_once __DIR__.'/../repositories/CategoriesRepository.php';

class ExpensesController extends AppController {
    private ExpensesRepository $expensesRepository;
    private CategoriesRepository $categoriesRepository;

    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->expensesRepository = new ExpensesRepository();
        $this->categoriesRepository = new CategoriesRepository();
    }

    public function index(): void
    {
        $userId = $this->currentUserId();
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'category_id' => trim($_GET['category_id'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
        ];

        $this->render("expenses", [
            "title" => "Expenses",
            "expenses" => $this->expensesRepository->getExpensesByUserId($userId, $filters),
            "categories" => $this->categoriesRepository->getCategoriesByUserId($userId),
            "filters" => $filters,
        ]);
    }

    public function create(): void
    {
        $userId = $this->currentUserId();
        $categories = $this->categoriesRepository->getCategoriesByUserId($userId);
        $expense = $this->emptyExpense();
        $errors = [];

        if ($this->isPost()) {
            $expense = $this->expenseFromRequest();
            $errors = $this->validateExpense($expense, $userId);

            if (empty($errors)) {
                $this->expensesRepository->createExpense(
                    $userId,
                    (int) $expense['category_id'],
                    $expense['name'],
                    $expense['amount'],
                    $expense['expense_date'],
                    $expense['note'] !== '' ? $expense['note'] : null
                );

                $this->setFlash('success', 'Wydatek został dodany.');
                $this->redirect('/expenses');
            }
        }

        $this->render("expense-form", [
            "title" => "Create expense",
            "_body_class" => "expense-entry-body",
            "mode" => "create",
            "expense" => $expense,
            "categories" => $categories,
            "errors" => $errors,
        ]);
    }

    public function edit(): void
    {
        $userId = $this->currentUserId();
        $id = (int) ($_GET['id'] ?? 0);
        $expense = $this->expensesRepository->getExpenseById($id, $userId);

        if ($expense === null) {
            $this->setFlash('error', 'Nie znaleziono wydatku.');
            $this->redirect('/expenses');
        }

        $errors = [];
        $categories = $this->categoriesRepository->getCategoriesByUserId($userId);

        if ($this->isPost()) {
            $expense = array_merge($expense, $this->expenseFromRequest());
            $errors = $this->validateExpense($expense, $userId);

            if (empty($errors)) {
                $this->expensesRepository->updateExpense(
                    $id,
                    $userId,
                    (int) $expense['category_id'],
                    $expense['name'],
                    $expense['amount'],
                    $expense['expense_date'],
                    $expense['note'] !== '' ? $expense['note'] : null
                );

                $this->setFlash('success', 'Wydatek został zaktualizowany.');
                $this->redirect('/expenses');
            }
        }

        $this->render("expense-form", [
            "title" => "Edit expense",
            "_body_class" => "expense-entry-body",
            "mode" => "edit",
            "expense" => $expense,
            "categories" => $categories,
            "errors" => $errors,
        ]);
    }

    public function delete(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/expenses');
        }

        $userId = $this->currentUserId();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || $this->expensesRepository->getExpenseById($id, $userId) === null) {
            $this->setFlash('error', 'Nie znaleziono wydatku do usunięcia.');
            $this->redirect('/expenses');
        }

        $this->expensesRepository->deleteExpense($id, $userId);
        $this->setFlash('success', 'Wydatek został usunięty.');
        $this->redirect('/expenses');
    }

    private function emptyExpense(): array
    {
        return [
            'name' => '',
            'amount' => '',
            'category_id' => '',
            'expense_date' => date('Y-m-d'),
            'note' => '',
        ];
    }

    private function expenseFromRequest(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'amount' => trim($_POST['amount'] ?? ''),
            'category_id' => trim($_POST['category_id'] ?? ''),
            'expense_date' => trim($_POST['expense_date'] ?? ''),
            'note' => trim($_POST['note'] ?? ''),
        ];
    }

    private function validateExpense(array $expense, int $userId): array
    {
        $errors = [];
        $nameLength = strlen($expense['name']);
        $noteLength = strlen($expense['note']);
        $categoryId = (int) $expense['category_id'];

        if ($nameLength < 3 || $nameLength > 100) {
            $errors[] = 'Nazwa musi mieć od 3 do 100 znaków.';
        }

        if (!is_numeric($expense['amount']) || (float) $expense['amount'] <= 0) {
            $errors[] = 'Kwota musi być liczbą większą od 0.';
        }

        if ($categoryId <= 0 || !$this->categoriesRepository->categoryBelongsToUser($categoryId, $userId)) {
            $errors[] = 'Wybrana kategoria jest nieprawidłowa.';
        }

        if (!$this->isValidDate($expense['expense_date'])) {
            $errors[] = 'Data wydatku jest nieprawidłowa.';
        }

        if ($noteLength > 500) {
            $errors[] = 'Notatka może mieć maksymalnie 500 znaków.';
        }

        return $errors;
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
