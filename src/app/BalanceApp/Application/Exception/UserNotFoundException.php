<?php

namespace App\BalanceApp\Application\Exception;

class UserNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Пользователь не найден', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
