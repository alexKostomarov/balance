<?php

namespace App\BalanceApp\Infrastructure\Repository;

use App\BalanceApp\Domain\Balance\Balance;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\User\UserId;
use App\Models\Balance as BalanceModel;


final class BalanceRepository implements BalanceRepositoryInterface
{
    public function getByUserId(UserId $userId): ?Balance
    {
        $model = BalanceModel::where('user_id', $userId->getValue())->first();

        if(!$model) return null;

        return new Balance(
            $userId,
            new Money($model->amount)
        );
    }

    public function save(Balance $balance): void
    {
        BalanceModel::updateOrCreate(
            ['user_id' => $balance->getUserId()->getValue()],
            [
                'amount' => $balance->getAmount()->getValue(),
            ]
        );
    }
}
