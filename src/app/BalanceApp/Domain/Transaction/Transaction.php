<?php

namespace App\BalanceApp\Domain\Transaction;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\User\UserId;

abstract class Transaction
{
    public function __construct(
        protected TransactionId      $id,
        protected TransactionType    $type,
        protected Money              $amount,
        protected string             $comment,
        protected UserId             $userId,
        protected \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    )
    {
    }

    public function getId(): TransactionId
    {
        return $this->id;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
