<?php

require_once 'Repository.php';

class CategoriesRepository extends Repository {

    private const DEFAULT_CATEGORIES = [
        ['name' => 'Food', 'icon' => 'fa-utensils', 'color' => '#25ff16'],
        ['name' => 'Transport', 'icon' => 'fa-car', 'color' => '#69a7ff'],
        ['name' => 'Retail', 'icon' => 'fa-bag-shopping', 'color' => '#b18cff'],
        ['name' => 'Fun', 'icon' => 'fa-masks-theater', 'color' => '#ff8fd5'],
        ['name' => 'Health', 'icon' => 'fa-briefcase-medical', 'color' => '#67e8f9'],
        ['name' => 'Bills', 'icon' => 'fa-bolt', 'color' => '#facc15'],
        ['name' => 'Travel', 'icon' => 'fa-plane', 'color' => '#fb923c'],
        ['name' => 'Other', 'icon' => 'fa-table-cells-large', 'color' => '#94a3b8'],
    ];

    public function ensureDefaultCategoriesForUser(int $userId): void
    {
        $this->ensureCategoryMetadataColumns();

        $query = $this->database->connect()->prepare(
            "
            INSERT INTO categories (user_id, name, icon, color, is_default)
            VALUES (:user_id, :name, :icon, :color, TRUE)
            ON CONFLICT (user_id, name) DO UPDATE
            SET icon = COALESCE(categories.icon, EXCLUDED.icon),
                color = COALESCE(categories.color, EXCLUDED.color),
                is_default = TRUE
            "
        );

        foreach (self::DEFAULT_CATEGORIES as $category) {
            $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $query->bindValue(':name', $category['name']);
            $query->bindValue(':icon', $category['icon']);
            $query->bindValue(':color', $category['color']);
            $query->execute();
        }
    }

    public function createCategory(int $userId, string $name, ?string $icon, ?string $color): void
    {
        $this->ensureCategoryMetadataColumns();

        $query = $this->database->connect()->prepare(
            "
            INSERT INTO categories (user_id, name, icon, color, is_default)
            VALUES (:user_id, :name, :icon, :color, FALSE)
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':name', $name);
        $query->bindValue(':icon', $icon);
        $query->bindValue(':color', $color);
        $query->execute();
    }

    public function getCategoryById(int $id, int $userId): ?array
    {
        $this->ensureCategoryMetadataColumns();

        $query = $this->database->connect()->prepare(
            "
            SELECT id, user_id, name, icon, color, CASE WHEN is_default THEN 1 ELSE 0 END AS is_default
            FROM categories
            WHERE id = :id AND user_id = :user_id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $category = $query->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    public function updateCategory(int $id, int $userId, string $name, ?string $icon, ?string $color): void
    {
        $this->ensureCategoryMetadataColumns();

        $query = $this->database->connect()->prepare(
            "
            UPDATE categories
            SET name = :name,
                icon = :icon,
                color = :color
            WHERE id = :id AND user_id = :user_id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':name', $name);
        $query->bindValue(':icon', $icon);
        $query->bindValue(':color', $color);
        $query->execute();
    }

    public function deleteCategory(int $id, int $userId): void
    {
        $query = $this->database->connect()->prepare(
            "
            DELETE FROM categories
            WHERE id = :id AND user_id = :user_id AND is_default = FALSE
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
    }

    public function categoryHasExpenses(int $id, int $userId): bool
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id
            FROM expenses
            WHERE category_id = :id AND user_id = :user_id
            LIMIT 1
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (bool) $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getCategoriesByUserId(int $userId): array
    {
        $this->ensureCategoryMetadataColumns();

        $query = $this->database->connect()->prepare(
            "
            SELECT id, name, icon, color, CASE WHEN is_default THEN 1 ELSE 0 END AS is_default
            FROM categories
            WHERE user_id = :user_id
            ORDER BY name ASC
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryStatsByUserId(int $userId): array
    {
        $this->ensureCategoryMetadataColumns();

        $query = $this->database->connect()->prepare(
            "
            SELECT
                c.id,
                c.name,
                c.icon,
                c.color,
                CASE WHEN c.is_default THEN 1 ELSE 0 END AS is_default,
                COUNT(e.id) AS expense_count,
                COALESCE(SUM(e.amount), 0) AS total
            FROM categories c
            LEFT JOIN expenses e ON e.category_id = c.id AND e.user_id = c.user_id
            WHERE c.user_id = :user_id
            GROUP BY c.id, c.name, c.icon, c.color, c.is_default
            ORDER BY total DESC, c.name ASC
            "
        );

        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function categoryBelongsToUser(int $categoryId, int $userId): bool
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id
            FROM categories
            WHERE id = :id AND user_id = :user_id
            "
        );

        $query->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return (bool) $query->fetch(PDO::FETCH_ASSOC);
    }

    private function ensureCategoryMetadataColumns(): void
    {
        $iconQuery = $this->database->connect()->prepare(
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS icon VARCHAR(30)"
        );
        $iconQuery->execute();

        $colorQuery = $this->database->connect()->prepare(
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS color VARCHAR(20)"
        );
        $colorQuery->execute();

        $defaultQuery = $this->database->connect()->prepare(
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS is_default BOOLEAN NOT NULL DEFAULT FALSE"
        );
        $defaultQuery->execute();
    }
}
