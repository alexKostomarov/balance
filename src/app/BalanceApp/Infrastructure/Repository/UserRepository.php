<?php

namespace App\BalanceApp\Infrastructure\Repository;

use App\BalanceApp\Domain\User\User;
use App\BalanceApp\Domain\User\UserId;
use App\BalanceApp\Domain\User\UserRepositoryInterface;
use App\Models\User as UserModel;

final class UserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->getValue());

        return $model ? new User(
            new UserId($model->id),
            $model->name,
            $model->email
        ) : null;
    }

    public function save(User $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->getId()->getValue()],
            [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ]
        );
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::where('email', $email)->first();

        return $model ? new User(
            new UserId($model->id),
            $model->name,
            $model->email
        ) : null;
    }

    public function findAll(): array
    {

        return UserModel::all()
            ->map(fn(UserModel $model) => new User(
                new UserId($model->id),
                $model->name,
                $model->email
            ))
            ->all();
    }
}
