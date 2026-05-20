<?php

class User {
    private int $id;
    private string $username;
    private string $email;
    private string $fullName;
    private bool $isActive;
    private ?string $createdAt;
    private ?string $password;

    public function __construct(
        int $id,
        string $username,
        string $email,
        string $fullName,
        bool $isActive,
        ?string $createdAt,
        ?string $password = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->fullName = $fullName;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->password = $password;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
