<?php

namespace App\BalanceApp\Domain\Money;

use App\BalanceApp\Domain\Exception\InsufficientFundsException;


final class Money
{
    public function __construct(
        private readonly int $amount//Деньги в центах, чтобы не округлять
    ) {
        if ($amount < 0) {
            throw new InsufficientFundsException('Amount cannot be negative');
        }
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
    public function getValue(): int
    {
        return $this->amount;
    }

    public function add(Money $other): Money
    {
        return new Money($this->amount + $other->getValue());
    }

    public function subtract(Money $other): Money
    {
        if ($other->getValue() > $this->amount) {
            throw new InsufficientFundsException("Not enough funds: {$other->getValue()}: {$this->amount}");
        }

        return new Money($this->amount - $other->getValue());
    }

}
