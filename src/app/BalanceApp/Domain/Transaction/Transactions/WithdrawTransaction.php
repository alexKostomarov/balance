<?php

namespace App\BalanceApp\Domain\Transaction\Transactions;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\Transaction;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\TransactionType;
use App\BalanceApp\Domain\User\UserId;

final class WithdrawTransaction extends Transaction
{
    public function __construct(
        TransactionId $id,
        Money $amount,
        string $comment,
        UserId $userId,
        \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {
        parent::__construct(
            $id,
            TransactionType::WITHDRAW,
            $amount, $comment,
            $userId,
            $createdAt
        );
    }
}
