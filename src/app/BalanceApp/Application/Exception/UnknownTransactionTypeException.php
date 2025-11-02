<?php

namespace App\BalanceApp\Application\Exception;

class UnknownTransactionTypeException extends \RuntimeException
{
    public function __construct(string $message = 'Неизвестнвй тип транзакции', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
