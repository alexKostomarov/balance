<?php

namespace App\BalanceApp\Domain\Balance;

use App\BalanceApp\Domain\User\UserId;

interface BalanceRepositoryInterface
{
    public function getByUserId(UserId $userId): ?Balance;
    public function save(Balance $balance): void;
}

