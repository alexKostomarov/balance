<?php

namespace App\BalanceApp\Application\Contract;

interface TransactionManagerInterface
{
    public function startTransaction(): void;
    public function commitTransaction(): void;
    public function rollbackTransaction(): void;
}
