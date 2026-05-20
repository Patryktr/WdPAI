<?php

require_once 'Repository.php';
require_once __DIR__.'/../entities/User.php';

class UsersRepository extends Repository {

    public function getUsers(): ?array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, username, email, full_name, is_active, created_at
            FROM users;
            "
        );
        $query->execute();

        return array_map(
            fn(array $row): User => $this->mapRowToUser($row),
            $query->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getUserByEmail(string $email): ?User
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, username, email, password, full_name, is_active, created_at
            FROM users
            WHERE email = :email
            "
        );
        $query->bindParam(':email', $email);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function getUserById(int $id): ?User
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, username, email, full_name, is_active, created_at
            FROM users
            WHERE id = :id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function getUserWithPasswordById(int $id): ?User
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, username, email, full_name, is_active, created_at, password
            FROM users
            WHERE id = :id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function createUser(
        string $username,
        string $email,
        string $passwordHash,
        string $fullName
    ): void {
        $query = $this->database->connect()->prepare(
            "
            INSERT INTO users (username, email, password, full_name)
            VALUES (:username, :email, :password, :full_name);
            "
        );

        $query->bindParam(':username', $username);
        $query->bindParam(':email', $email);
        $query->bindParam(':password', $passwordHash);
        $query->bindParam(':full_name', $fullName);
        $query->execute();
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $query = $this->database->connect()->prepare(
            "
            UPDATE users
            SET password = :password
            WHERE id = :id
            "
        );

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':password', $passwordHash);
        $query->execute();
    }

    private function mapRowToUser(array $row): User
    {
        return new User(
            (int) $row['id'],
            (string) $row['username'],
            (string) $row['email'],
            (string) $row['full_name'],
            $this->toBool($row['is_active']),
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['password']) ? (string) $row['password'] : null
        );
    }

    private function toBool(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 't' || $value === 'true';
    }
}
