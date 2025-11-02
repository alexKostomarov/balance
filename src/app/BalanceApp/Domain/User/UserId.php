<?php

namespace App\BalanceApp\Domain\User;

use Ramsey\Uuid\Uuid;

final class UserId
{
    public function __construct(
        private readonly string $id
    ) {}

    public static function genereateId(): string
    {
        return Uuid::uuid4()->toString();
    }
    public function getValue(): string
    {
        return $this->id;
    }

    public function equals(UserId $other): bool
    {
        return $this->id === $other->getValue();
    }
}
