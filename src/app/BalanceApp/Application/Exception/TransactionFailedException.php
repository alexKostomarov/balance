<?php

namespace App\BalanceApp\Application\Exception;

class TransactionFailedException extends \RuntimeException
{
    public function __construct(string $message = 'Transaction failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
