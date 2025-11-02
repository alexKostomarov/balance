<?php

namespace App\BalanceApp\Application\Exception;

class BalanceNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Баланс не найден', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
