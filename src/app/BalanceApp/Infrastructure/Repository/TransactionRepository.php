<?php

namespace App\BalanceApp\Infrastructure\Repository;

use App\BalanceApp\Domain\Transaction\Transaction;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Infrastructure\Repository\Mapper\TransactionMapper;
use App\Models\Transaction as TransactionModel;


final class TransactionRepository implements TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void
    {
        $type = $transaction->getType();

        $model = TransactionMapper::toModel($transaction);

        $model->save();

    }

    public function findByUserId(string $userId): array
    {

        return TransactionModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($model) => TransactionMapper::fromModel($model))
            ->all();
    }

    public function findById(TransactionId $id): ?Transaction
    {
        $model = TransactionModel::find($id->getValue());

        if (!$model) return null;



        return TransactionMapper::fromModel($model);

    }

}
