<?php

namespace App\BalanceApp\Domain\Transaction\Transactions;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\Transaction;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\TransactionType;
use App\BalanceApp\Domain\User\UserId;

final class TransferTransaction extends Transaction
{
    public function __construct(
        TransactionId $id,
        TransactionType $type, // transfer_in или transfer_out
        Money $amount,
        string $comment,
        UserId $userId,
        private UserId $relatedUserId,
        \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {
        parent::__construct($id, $type, $amount, $comment, $userId, $createdAt);
    }

    public function getRelatedUserId(): UserId
    {
        return $this->relatedUserId;
    }
}
