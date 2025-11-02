<?php

namespace App\BalanceApp\Application\Service;

use App\BalanceApp\Domain\User\User;
use App\BalanceApp\Domain\User\UserRepositoryInterface;

final class ListUsersService
{
    public function __construct(private readonly UserRepositoryInterface $repo) {}

    /**
     * @return User[]
     */
    public function list(): array
    {
        return $this->repo->findAll();
    }
}
