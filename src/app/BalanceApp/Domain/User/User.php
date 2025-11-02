<?php

namespace App\BalanceApp\Domain\User;

final class User
{
    public function __construct(
        private readonly UserId $id,
        private string $name,
        private string $email
    ) {}

    /** TODO: реализовать object Value Email */

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $newName): void
    {
        $this->name = $newName;
    }
    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $newName): void
    {
        $this->email = $newName;
    }
}

