<?php

namespace App\BalanceApp\Domain\Exception;

class InsufficientFundsException extends \RuntimeException
{
    public function __construct(string $message = 'Не достаточно средств', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
