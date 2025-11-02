<?php

namespace App\BalanceApp\Application\UseCaseInput;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\User\UserId;

final class WithdrawInput
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Money $amount,
        public readonly string $comment = ''
    ) {}
}
