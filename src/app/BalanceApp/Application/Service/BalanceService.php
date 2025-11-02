<?php

namespace App\BalanceApp\Application\Service;

use App\BalanceApp\Application\Dto\BalanceDto;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\User\UserId;

final class BalanceService
{
    public function __construct(
        private readonly BalanceRepositoryInterface $balanceRepository
    ) {}

    public function getBalance(UserId $userId): ?BalanceDto
    {
        $balance = $this->balanceRepository->getByUserId($userId);

        if (!$balance) {
            return null;
        }

        return new BalanceDto($balance->getUserId()->getValue(), $balance->getAmount()->getValue());
    }
}
