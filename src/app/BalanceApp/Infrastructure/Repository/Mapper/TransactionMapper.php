<?php

namespace App\BalanceApp\Infrastructure\Repository\Mapper;

use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\Transaction;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\Transactions\DepositTransaction;
use App\BalanceApp\Domain\Transaction\Transactions\TransferTransaction;
use App\BalanceApp\Domain\Transaction\Transactions\WithdrawTransaction;
use App\BalanceApp\Domain\Transaction\TransactionType;
use App\BalanceApp\Domain\User\UserId;
use App\Models\Transaction as TransactionModel;

;

final class TransactionMapper
{
    public static function fromModel(TransactionModel $model): Transaction
    {
        $type = TransactionType::from($model->type);

        return match ($type) {
            TransactionType::DEPOSIT => new DepositTransaction(
                new TransactionId($model->id),
                new Money($model->amount),
                $model->comment,
                new UserId($model->user_id),
                $model->created_at
            ),

            TransactionType::WITHDRAW => new WithdrawTransaction(
                new TransactionId($model->id),
                new Money($model->amount),
                $model->comment,
                new UserId($model->user_id),
                $model->created_at
            ),

            TransactionType::TRANSFER_IN, TransactionType::TRANSFER_OUT => new TransferTransaction(
                new TransactionId($model->id),
                $type,
                new Money($model->amount),
                $model->comment,
                new UserId($model->user_id),
                new UserId($model->related_user_id),
                $model->created_at
            ),
        };
    }

    public static function toModel(Transaction $transaction): TransactionModel
    {
        $model = new TransactionModel();
        $model->id = $transaction->getId()->getValue();
        $model->user_id = $transaction->getUserId()->getValue();
        $model->amount = $transaction->getAmount()->getValue();
        $model->comment = $transaction->getComment();
        $model->type = $transaction->getType()->value;
        $model->created_at = $transaction->getCreatedAt();

        if ($transaction instanceof TransferTransaction) {
            $model->related_user_id = $transaction->getRelatedUserId()->getValue();
        }

        return $model;
    }
}
