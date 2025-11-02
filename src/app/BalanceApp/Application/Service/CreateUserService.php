<?php

namespace App\BalanceApp\Application\Service;

use App\BalanceApp\Application\UseCaseInput\CreateUserInput;
use App\BalanceApp\Domain\User\User;
use App\BalanceApp\Domain\User\UserId;
use App\BalanceApp\Domain\User\UserRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class CreateUserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function create(CreateUserInput $input): User
    {
        if ($this->userRepository->findByEmail($input->email)) {
            throw new \RuntimeException('User with this email already exists');
        }

        $userId = new UserId(Uuid::uuid4()->toString());

        $user = new User(
            $userId,
            $input->name,
            $input->email
        );

        $this->userRepository->save($user);

        return $user;
    }
}
