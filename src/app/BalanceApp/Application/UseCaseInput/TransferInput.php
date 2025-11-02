<?php

namespace App\BalanceApp\Application\UseCaseInput;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\User\UserId;

final class TransferInput
{
    public function __construct(
        public readonly UserId $from,
        public readonly UserId $to,
        public readonly Money $amount,
        public readonly string $comment = ''
    ) {}
}
