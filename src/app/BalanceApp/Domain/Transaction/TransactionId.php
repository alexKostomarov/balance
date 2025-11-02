<?php

namespace App\BalanceApp\Domain\Transaction;

use Ramsey\Uuid\Uuid;

final class TransactionId
{
    public function __construct(private readonly string $uuid) {}

    public function getValue(): string
    {
        return $this->uuid;
    }

    public static function genereateId(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function equals(TransactionId $other): bool
    {
        return $this->uuid === $other->getValue();
    }
}
