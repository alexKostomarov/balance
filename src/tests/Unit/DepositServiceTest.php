<?php

namespace Tests\Unit;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\Service\DepositService;
use App\BalanceApp\Application\UseCaseInput\DepositInput;
use App\BalanceApp\Domain\Balance\Balance;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\Transaction\Transactions\DepositTransaction;
use App\BalanceApp\Domain\User\User;
use App\BalanceApp\Domain\User\UserId;
use App\BalanceApp\Domain\User\UserRepositoryInterface;


use PHPUnit\Framework\TestCase;

class DepositServiceTest extends TestCase
{
    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     * Баланс увеличен, транзакция создана, всё сохранено
     */
    public function test_successful_deposit_creates_transaction_and_updates_balance()
    {
        $input = new DepositInput(
            new UserId(UserId::genereateId()),
            new Money(1000),
            'Пополнение'
        );

        //Эмуляция нахождения юзера в базе
        $user = new User(
            $input->userId,
            'user',
            'user@example.com'
        );
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $userRepo->method('findById')->with($input->userId)->willReturn($user);

        //Объект баланса , эмуляция присутсвия в базе, эмуляция записи
        $balance = new Balance($input->userId, new Money(0));

        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->with($input->userId)->willReturn($balance);
        $balanceRepo->expects($this->once())->method('save')->with($balance);

        //Эмуояция репозиторя
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $transactionRepo->expects($this->once())->method('save')
            ->with($this->callback(fn($t) => $t instanceof DepositTransaction));

        $txManager = $this->createMock(TransactionManagerInterface::class);
        $txManager->expects($this->once())->method('startTransaction');
        $txManager->expects($this->once())->method('commitTransaction');

        //вызов сервиса депозитов с моками
        $service = new DepositService($balanceRepo, $transactionRepo, $userRepo, $txManager);



        $result = $service->deposit($input);

        $this->assertEquals($input->userId->getValue(), $result->userId);
        $this->assertEquals('deposit', $result->type);
        $this->assertEquals(1000, $result->amount);
        $this->assertEquals('Пополнение', $result->comment);
    }

    public function test_deposit_rolls_back_on_error()
    {
        $input = new DepositInput(
            new UserId(UserId::genereateId()),
            new Money(500),
            'Ошибка при сохранении'
        );

        // Настоящий пользователь
        $user = new User(
            $input->userId,
            'Test User',
            'test@example.com'
        );

        // Настоящий баланс
        $balance = new Balance(
            $input->userId,
            new Money(0)
        );

        // Репозиторий пользователя
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $userRepo->method('findById')->willReturn($user);

        // Репозиторий баланса, выбрасывает исключение при save()
        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturn($balance);
        $balanceRepo->method('save')->willThrowException(new \Exception('DB error'));

        // Репозиторий транзакций
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);

        // Транзакционный менеджер
        $txManager = $this->createMock(TransactionManagerInterface::class);
        $txManager->expects($this->once())->method('startTransaction');
        $txManager->expects($this->once())->method('rollbackTransaction');
        $txManager->expects($this->never())->method('commitTransaction');

        $service = new DepositService($balanceRepo, $transactionRepo, $userRepo, $txManager);

        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionMessage('Withdraw failed: DB error');

        $service->deposit($input);
    }

    public function test_deposit_creates_new_balance_if_missing()
    {
        $input = new DepositInput(
            new UserId(UserId::genereateId()),
            new Money(700),
            'Первый депозит'
        );

        // Реальный пользователь
        $user = new \App\BalanceApp\Domain\User\User(
            $input->userId,
            'Новый пользователь',
            'new@example.com'
        );

        // Репозиторий пользователя
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $userRepo->method('findById')->willReturn($user);

        // Репозиторий баланса — возвращает null
        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturn(null);
        $balanceRepo->expects($this->once())->method('save')
            ->with($this->callback(function ($balance) use ($input) {
                return $balance instanceof \App\BalanceApp\Domain\Balance\Balance &&
                    $balance->getUserId()->equals($input->userId) &&
                    $balance->getAmount()->getValue() === 700;
            }));

        // Репозиторий транзакций
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $transactionRepo->expects($this->once())->method('save');

        // Транзакционный менеджер
        $txManager = $this->createMock(TransactionManagerInterface::class);
        $txManager->expects($this->once())->method('startTransaction');
        $txManager->expects($this->once())->method('commitTransaction');

        $service = new DepositService($balanceRepo, $transactionRepo, $userRepo, $txManager);

        $result = $service->deposit($input);

        $this->assertEquals($input->userId->getValue(), $result->userId);
        $this->assertEquals('deposit', $result->type);
        $this->assertEquals(700, $result->amount);
        $this->assertEquals('Первый депозит', $result->comment);
    }


}
