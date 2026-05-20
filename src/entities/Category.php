<?php

class Category {
    private int $id;
    private int $userId;
    private string $name;
    private ?string $icon;
    private ?string $color;
    private bool $isDefault;
    private ?string $createdAt;

    public function __construct(
        int $id,
        int $userId,
        string $name,
        ?string $icon,
        ?string $color,
        bool $isDefault,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->icon = $icon;
        $this->color = $color;
        $this->isDefault = $isDefault;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
}
