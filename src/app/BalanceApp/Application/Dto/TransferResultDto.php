<?php

namespace App\BalanceApp\Application\Dto;

final class TransferResultDto
{
    public function __construct(
        public readonly string $userFrom,
        public readonly string $userTo,
        public readonly int $amount,
        public readonly string $comment
    ) {}
}

