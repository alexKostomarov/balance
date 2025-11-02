<?php

namespace Tests\Unit;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Application\Exception\BalanceNotFoundException;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\Exception\UserNotFoundException;
use App\BalanceApp\Application\Service\TransferService;
use App\BalanceApp\Application\UseCaseInput\TransferInput;
use App\BalanceApp\Domain\Balance\Balance;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\Transaction\Transactions\TransferTransaction;
use App\BalanceApp\Domain\User\User;
use App\BalanceApp\Domain\User\UserId;
use App\BalanceApp\Domain\User\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class TransferServiceTest extends TestCase
{
    public function test_successful_transfer_creates_transactions_and_updates_balances()
    {
        $fromId = new UserId(UserId::genereateId());
        $toId = new UserId(UserId::genereateId());
        $userTo = new User($toId, 'Получатель', 'to@example.com');

        $input = new TransferInput(
            $fromId,
            $toId,
            new Money(500),
            'Перевод средств'
        );

        // Баланс отправителя с достаточной суммой
        $balanceFrom = new Balance($fromId, new Money(1000));

        // Баланс получателя с начальной суммой
        $balanceTo = new Balance($toId, new Money(200));

        // Репозиторий баланса
        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturnCallback(function ($userId) use ($fromId, $toId, $balanceFrom, $balanceTo) {
            return $userId->equals($fromId) ? $balanceFrom : $balanceTo;
        });
        $balanceRepo->expects($this->exactly(2))->method('save');

        // Репозиторий пользователя
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $userRepo->method('findById')->with($toId)->willReturn($userTo);

        // Репозиторий транзакций
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $transactionRepo->expects($this->exactly(2))->method('save')
            ->with($this->callback(fn($t) => $t instanceof TransferTransaction));

        // Транзакционный менеджер
        $txManager = $this->createMock(TransactionManagerInterface::class);
        $txManager->expects($this->once())->method('startTransaction');
        $txManager->expects($this->once())->method('commitTransaction');

        $service = new TransferService($balanceRepo, $transactionRepo, $userRepo, $txManager);

        $result = $service->transfer($input);

        $this->assertEquals($input->from->getValue(), $result->userFrom);
        $this->assertEquals($input->to->getValue(), $result->userTo);
        $this->assertEquals(500, $result->amount);
        $this->assertEquals('Перевод средств', $result->comment);
    }

    public function test_transfer_throws_if_sender_balance_not_found()
    {
        $fromId = new UserId(UserId::genereateId());
        $toId = new UserId(UserId::genereateId());

        $input = new TransferInput(
            $fromId,
            $toId,
            new Money(100),
            'Перевод средств'
        );

        // Репозиторий баланса: отправитель отсутствует
        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->with($fromId)->willReturn(null);

        $userRepo = $this->createStub(UserRepositoryInterface::class);
        $transactionRepo = $this->createStub(TransactionRepositoryInterface::class);
        $txManager = $this->createStub(TransactionManagerInterface::class);

        $service = new TransferService($balanceRepo, $transactionRepo, $userRepo, $txManager);

        $this->expectException(BalanceNotFoundException::class);
        $this->expectExceptionMessage('Баланс отправителя не найден');

        $service->transfer($input);
    }

    public function test_transfer_throws_if_recipient_not_found()
    {
        $fromId = new UserId(UserId::genereateId());
        $toId = new UserId(UserId::genereateId());

        $input = new TransferInput(
            $fromId,
            $toId,
            new Money(100),
            'Перевод средств'
        );

        // Баланс отправителя есть
        $balanceFrom = new Balance($fromId, new Money(1000));

        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->with($fromId)->willReturn($balanceFrom);

        // Репозиторий пользователя — получатель не найден
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $userRepo->method('findById')->with($toId)->willReturn(null);

        $transactionRepo = $this->createStub(TransactionRepositoryInterface::class);
        $txManager = $this->createStub(TransactionManagerInterface::class);

        $service = new TransferService($balanceRepo, $transactionRepo, $userRepo, $txManager);

        $this->expectException(UserNotFoundException::class);

        $service->transfer($input);
    }

    public function test_transfer_rolls_back_on_error()
    {
        $fromId = new UserId(UserId::genereateId());
        $toId = new UserId(UserId::genereateId());

        $input = new TransferInput(
            $fromId,
            $toId,
            new Money(300),
            'Ошибка при переводе'
        );

        $balanceFrom = new Balance($fromId, new Money(1000));
        $balanceTo = new Balance($toId, new Money(0));
        $userTo = new User($toId, 'Получатель', 'to@example.com');

        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturnCallback(function ($userId) use ($fromId, $toId, $balanceFrom, $balanceTo) {
            return $userId->equals($fromId) ? $balanceFrom : $balanceTo;
        });
        $balanceRepo->method('save')->willThrowException(new \Exception('DB error'));

        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $userRepo->method('findById')->with($toId)->willReturn($userTo);

        $transactionRepo = $this->createStub(TransactionRepositoryInterface::class);

        $txManager = $this->createMock(TransactionManagerInterface::class);
        $txManager->expects($this->once())->method('startTransaction');
        $txManager->expects($this->once())->method('rollbackTransaction');
        $txManager->expects($this->never())->method('commitTransaction');

        $service = new TransferService($balanceRepo, $transactionRepo, $userRepo, $txManager);

        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionMessage('Transaction failed: DB error');

        $service->transfer($input);
    }


}
