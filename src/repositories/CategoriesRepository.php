<?php

require_once 'Repository.php';

class CategoriesRepository extends Repository {

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
