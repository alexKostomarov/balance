<?php

namespace App\BalanceApp\Application\Dto;

final readonly class BalanceDto
{
    public function __construct(
        public string $userId,
        public int    $balance
    ) {}
}
