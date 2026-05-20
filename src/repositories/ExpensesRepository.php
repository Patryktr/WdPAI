<?php

require_once 'Repository.php';
require_once __DIR__.'/../entities/Expense.php';

class ExpensesRepository extends Repository {

    public function getRecentExpensesByUserId(int $userId, int $limit): array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT e.id, e.user_id, e.category_id, e.name, e.amount, e.expense_date, e.note, c.name AS category_name
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE e.user_id = :user_id
            ORDER BY e.expense_date DESC, e.id DESC
            LIMIT :limit
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();

        return array_map(function (array $row): array {
            return [
                'expense' => $this->mapRowToExpense($row),
                'category_name' => (string) $row['category_name'],
            ];
        }, $query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getMonthlyTotalByUserId(int $userId): float
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM expenses
            WHERE user_id = :user_id
              AND expense_date >= DATE_TRUNC('month', CURRENT_DATE)
              AND expense_date < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (float) $query->fetchColumn();
    }

    public function getMonthlyCountByUserId(int $userId): int
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT COUNT(*) AS total
            FROM expenses
            WHERE user_id = :user_id
              AND expense_date >= DATE_TRUNC('month', CURRENT_DATE)
              AND expense_date < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (int) $query->fetchColumn();
    }

    public function getTotalByUserId(int $userId): float
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM expenses
            WHERE user_id = :user_id
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (float) $query->fetchColumn();
    }

    public function getAverageExpenseByUserId(int $userId): float
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT COALESCE(AVG(amount), 0) AS average_amount
            FROM expenses
            WHERE user_id = :user_id
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (float) $query->fetchColumn();
    }

    public function getExpensesCountByUserId(int $userId): int
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT COUNT(*) AS total
            FROM expenses
            WHERE user_id = :user_id
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (int) $query->fetchColumn();
    }

    public function getMonthlySummaryByUserId(int $userId): array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT
                TO_CHAR(DATE_TRUNC('month', expense_date), 'YYYY-MM') AS month_key,
                TO_CHAR(DATE_TRUNC('month', expense_date), 'Mon YYYY') AS month_label,
                COALESCE(SUM(amount), 0) AS total,
                COUNT(*) AS expense_count
            FROM expenses
            WHERE user_id = :user_id
            GROUP BY DATE_TRUNC('month', expense_date)
            ORDER BY DATE_TRUNC('month', expense_date) ASC
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBiggestExpenseByUserId(int $userId): ?array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT e.id, e.user_id, e.category_id, e.name, e.amount, e.expense_date, e.note, c.name AS category_name
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE e.user_id = :user_id
            ORDER BY e.amount DESC, e.expense_date DESC, e.id DESC
            LIMIT 1
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'expense' => $this->mapRowToExpense($row),
            'category_name' => (string) $row['category_name'],
        ];
    }

    public function getCategorySummaryByUserId(int $userId): array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT c.name, COALESCE(SUM(e.amount), 0) AS total, COUNT(e.id) AS expense_count
            FROM categories c
            LEFT JOIN expenses e ON e.category_id = c.id AND e.user_id = c.user_id
            WHERE c.user_id = :user_id
            GROUP BY c.id, c.name
            ORDER BY total DESC, c.name ASC
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return array_map(static function (array $row): array {
            return [
                'name' => (string) $row['name'],
                'total' => (float) $row['total'],
                'expense_count' => (int) $row['expense_count'],
            ];
        }, $query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getExpensesByUserId(int $userId, array $filters = []): array
    {
        $conditions = ["e.user_id = :user_id"];
        $params = [':user_id' => $userId];

        if (!empty($filters['search'])) {
            $conditions[] = "(LOWER(e.name) LIKE LOWER(:search) OR LOWER(COALESCE(e.note, '')) LIKE LOWER(:search))";
            $params[':search'] = '%'.$filters['search'].'%';
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = "e.category_id = :category_id";
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "e.expense_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "e.expense_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql = "
            SELECT e.id, e.user_id, e.category_id, e.name, e.amount, e.expense_date, e.note, c.name AS category_name
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE ".implode(' AND ', $conditions)."
            ORDER BY e.expense_date DESC, e.id DESC
        ";

        $query = $this->database->connect()->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':user_id' || $key === ':category_id') {
                $query->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $query->bindValue($key, $value);
            }
        }

        $query->execute();

        return array_map(
            fn(array $row): Expense => $this->mapRowToExpense($row),
            $query->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getExpenseById(int $id, int $userId): ?Expense
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, user_id, category_id, name, amount, expense_date, note
            FROM expenses
            WHERE id = :id AND user_id = :user_id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToExpense($row);
    }

    public function createExpense(
        int $userId,
        int $categoryId,
        string $name,
        string $amount,
        string $expenseDate,
        ?string $note
    ): void {
        $query = $this->database->connect()->prepare(
            "
            INSERT INTO expenses (user_id, category_id, name, amount, expense_date, note)
            VALUES (:user_id, :category_id, :name, :amount, :expense_date, :note)
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $query->bindValue(':name', $name);
        $query->bindValue(':amount', $amount);
        $query->bindValue(':expense_date', $expenseDate);
        $query->bindValue(':note', $note);
        $query->execute();
    }

    public function updateExpense(
        int $id,
        int $userId,
        int $categoryId,
        string $name,
        string $amount,
        string $expenseDate,
        ?string $note
    ): void {
        $query = $this->database->connect()->prepare(
            "
            UPDATE expenses
            SET category_id = :category_id,
                name = :name,
                amount = :amount,
                expense_date = :expense_date,
                note = :note,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND user_id = :user_id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $query->bindValue(':name', $name);
        $query->bindValue(':amount', $amount);
        $query->bindValue(':expense_date', $expenseDate);
        $query->bindValue(':note', $note);
        $query->execute();
    }

    public function deleteExpense(int $id, int $userId): void
    {
        $query = $this->database->connect()->prepare(
            "
            DELETE FROM expenses
            WHERE id = :id AND user_id = :user_id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
    }

    private function mapRowToExpense(array $row): Expense
    {
        return new Expense(
            (int) $row['id'],
            (int) $row['user_id'],
            (int) $row['category_id'],
            (string) $row['name'],
            (string) $row['amount'],
            (string) $row['expense_date'],
            isset($row['note']) ? (string) $row['note'] : null,
            isset($row['category_name']) ? (string) $row['category_name'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null
        );
    }
}
