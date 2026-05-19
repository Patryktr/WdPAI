<?php

require_once 'Repository.php';

class CategoriesRepository extends Repository {

    private const DEFAULT_CATEGORIES = [
        'Food',
        'Transport',
        'Retail',
        'Fun',
        'Health',
        'Bills',
        'Travel',
        'Other',
    ];

    public function ensureDefaultCategoriesForUser(int $userId): void
    {
        $query = $this->database->connect()->prepare(
            "
            INSERT INTO categories (user_id, name)
            VALUES (:user_id, :name)
            ON CONFLICT (user_id, name) DO NOTHING
            "
        );

        foreach (self::DEFAULT_CATEGORIES as $categoryName) {
            $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $query->bindValue(':name', $categoryName);
            $query->execute();
        }
    }

    public function getCategoriesByUserId(int $userId): array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, name
            FROM categories
            WHERE user_id = :user_id
            ORDER BY name ASC
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
}
