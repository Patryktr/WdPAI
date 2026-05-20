<?php

class Expense {
    private int $id;
    private int $userId;
    private int $categoryId;
    private string $name;
    private string $amount;
    private string $expenseDate;
    private ?string $note;
    private ?string $categoryName;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        int $id,
        int $userId,
        int $categoryId,
        string $name,
        string $amount,
        string $expenseDate,
        ?string $note,
        ?string $categoryName = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->amount = $amount;
        $this->expenseDate = $expenseDate;
        $this->note = $note;
        $this->categoryName = $categoryName;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getExpenseDate(): string
    {
        return $this->expenseDate;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
