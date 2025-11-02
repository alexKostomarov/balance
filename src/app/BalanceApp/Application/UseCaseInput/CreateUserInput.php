<?php

namespace App\BalanceApp\Application\UseCaseInput;

final class CreateUserInput
{
    public function __construct(
        public readonly string $name,
        public readonly string $email
    ) {}
}
