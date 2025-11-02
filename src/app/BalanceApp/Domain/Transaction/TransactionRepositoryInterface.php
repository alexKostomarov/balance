<?php

namespace App\BalanceApp\Domain\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;

    /**
     * @return Transaction[]
     */
    public function findByUserId(string $userId): array;

    public function findById(TransactionId $id): ?Transaction;
}
