<?php

namespace App\BalanceApp\Infrastructure\Providers;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\User\UserRepositoryInterface;
use App\BalanceApp\Infrastructure\Repository\BalanceRepository;
use App\BalanceApp\Infrastructure\Repository\TransactionRepository;
use App\BalanceApp\Infrastructure\Repository\UserRepository;
use App\BalanceApp\Infrastructure\Transaction\LaravelTransactionManager;
use Illuminate\Support\ServiceProvider;

class InfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BalanceRepositoryInterface::class, BalanceRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(TransactionManagerInterface::class, LaravelTransactionManager::class);
    }
}
