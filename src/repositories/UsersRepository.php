<?php

require_once 'Repository.php';

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

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail(string $email): ?array
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

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getUserById(int $id): ?array
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

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getUserWithPasswordById(int $id): ?array
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

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
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
}
