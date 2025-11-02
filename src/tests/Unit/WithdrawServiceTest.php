<?php

namespace Tests\Unit;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Application\Exception\BalanceNotFoundException;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\Service\WithdrawService;
use App\BalanceApp\Application\UseCaseInput\WithdrawInput;
use App\BalanceApp\Domain\Balance\Balance;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Exception\InsufficientFundsException;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\Transaction\Transactions\WithdrawTransaction;
use App\BalanceApp\Domain\User\UserId;
use PHPUnit\Framework\TestCase;

class WithdrawServiceTest extends TestCase
{
    public function test_successful_withdraw_creates_transaction_and_updates_balance()
    {
        $input = new WithdrawInput(
            new UserId(UserId::genereateId()),
            new Money(500),
            'Снятие средств'
        );

        //Объект баланса , эмуляция присутсвия в базе, эмуляция записи
        $balance = new Balance($input->userId, new Money(1000));

        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturn($balance);
        $balanceRepo->expects($this->once())->method('save')->with($balance);


        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $transactionRepo->expects($this->once())->method('save')
            ->with($this->callback(fn($t) => $t instanceof WithdrawTransaction));

        $txManager = $this->createStub(TransactionManagerInterface::class);

        $service = new WithdrawService($balanceRepo, $transactionRepo, $txManager);

        $result = $service->withdraw($input);

        $this->assertEquals($input->userId->getValue(), $result->userId);
        $this->assertEquals('withdraw', $result->type);
        $this->assertEquals(500, $result->amount);
        $this->assertEquals('Снятие средств', $result->comment);
    }

    public function test_withdraw_throws_if_balance_not_found()
    {
        $input = new WithdrawInput(
            new UserId(UserId::genereateId()),
            new Money(500),
            'Снятие средств'
        );

        // Репозиторий баланса возвращает null
        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturn(null);

        $transactionRepo = $this->createStub(TransactionRepositoryInterface::class);
        $txManager = $this->createStub(TransactionManagerInterface::class);

        $service = new WithdrawService($balanceRepo, $transactionRepo, $txManager);

        $this->expectException(BalanceNotFoundException::class);
        $this->expectExceptionMessage('Баланс не найден');

        $service->withdraw($input);
    }

    public function test_withdraw_throws_if_insufficient_funds()
    {
        $input = new WithdrawInput(
            new UserId(UserId::genereateId()),
            new Money(1500),
            'Попытка снять больше, чем есть'
        );

        // Баланс с недостаточной суммой
        $balance = new Balance($input->userId, new Money(1000));

        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturn($balance);
        $balanceRepo->expects($this->never())->method('save');

        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $transactionRepo->expects($this->never())->method('save');

        $txManager = $this->createStub(TransactionManagerInterface::class);

        $service = new WithdrawService($balanceRepo, $transactionRepo, $txManager);

        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Not enough funds: 1500: 1000');

        $service->withdraw($input);
    }

    public function test_withdraw_rolls_back_on_error()
    {
        $input = new WithdrawInput(
            new UserId(UserId::genereateId()),
            new Money(300),
            'Сбой при сохранении'
        );

        // Реальный баланс с достаточной суммой
        $balance = new Balance($input->userId, new Money(1000));

        // Репозиторий баланса: getByUserId возвращает баланс, save выбрасывает исключение
        $balanceRepo = $this->createMock(BalanceRepositoryInterface::class);
        $balanceRepo->method('getByUserId')->willReturn($balance);
        $balanceRepo->method('save')->willThrowException(new \Exception('DB error'));

        // Репозиторий транзакций — не должен быть вызван
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $transactionRepo->expects($this->never())->method('save');

        // Транзакционный менеджер — должен вызвать rollback
        $txManager = $this->createMock(TransactionManagerInterface::class);
        $txManager->expects($this->once())->method('rollbackTransaction');

        $service = new WithdrawService($balanceRepo, $transactionRepo, $txManager);

        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionMessage('Withdraw failed: DB error');

        $service->withdraw($input);
    }



}
