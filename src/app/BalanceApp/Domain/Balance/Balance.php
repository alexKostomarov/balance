<?php

namespace App\BalanceApp\Domain\Balance;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\User\UserId;

final class Balance
{
    public function __construct(
        private readonly UserId $userId,
        private Money $amount
    ) {}

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function deposit(Money $money): void
    {
        $this->amount = $this->amount->add($money);
    }

    public function withdraw(Money $money): void
    {
        $this->amount = $this->amount->subtract($money);
    }
}
