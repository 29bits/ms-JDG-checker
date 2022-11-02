<?php

namespace App\DTO;

class Company
{
    private string $name;
    private bool $isActive;
    private bool $hasPkd;

    public function __construct(string $name, bool $isActive, bool $hasPkd)
    {
        $this->name = $name;
        $this->isActive = $isActive;
        $this->hasPkd = $hasPkd;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function hasPkd(): bool
    {
        return $this->hasPkd;
    }

    public function isCorrect(string $name): bool
    {
        return strtolower($this->name) === strtolower($name) && $this->isActive && $this->hasPkd;
    }
}
