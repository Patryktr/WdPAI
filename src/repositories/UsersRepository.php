<?php

require_once 'Repository.php';

class UsersRepository extends Repository {

    public function getUsers(): ?array 
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT id, username, email, password, full_name, is_active, created_at
            FROM users;
            "
        );
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUserByEmail(string $email) 
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
        return $user;
    }

    public function createUser(
        string $username,
        string $email,
        string $hashedPassword,
        string $fullName,
        bool $isActive = true
    ): void {
        $query = $this->database->connect()->prepare(
            "
            INSERT INTO users (username, email, password, full_name, is_active)
            VALUES (:username, :email, :password, :full_name, :is_active);
            "
        );

        $query->bindParam(':username', $username);
        $query->bindParam(':email', $email);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':full_name', $fullName);
        $query->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
        $query->execute();
    }
}
