<?php

namespace App\BalanceApp\Application\Dto;

final class TransactionResultDto
{
    public function __construct(
        public readonly string $userId,
        public readonly string $type, // deposit, withdraw
        public readonly int $amount,
        public readonly ?string $comment = null
    ) {}
}

