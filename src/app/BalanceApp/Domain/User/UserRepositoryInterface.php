<?php

namespace App\BalanceApp\Domain\User;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;
    public function save(User $user): void;

    public function findByEmail(string $email);

    public function findAll(): array;
}
