<?php

namespace App\BalanceApp\Application\Service;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Application\Dto\TransactionResultDto;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\Exception\UserNotFoundException;
use App\BalanceApp\Application\UseCaseInput\DepositInput;
use App\BalanceApp\Domain\Balance\Balance;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\Transaction\Transactions\DepositTransaction;
use App\BalanceApp\Domain\User\UserRepositoryInterface;


final class DepositService
{
    public function __construct(
        private readonly BalanceRepositoryInterface $balanceRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly UserRepositoryInterface $userRepository,
        private  readonly TransactionManagerInterface $transactionManager
    ) {}

    public function deposit(DepositInput $input): TransactionResultDto
    {
        $user = $this->userRepository->findById($input->userId);

        if(!$user){
            throw new UserNotFoundException();
        }

        $balance = $this->balanceRepository->getByUserId($input->userId);

        if (!$balance) {
            $balance = new Balance($input->userId, new Money(0));
        }

        $balance->deposit($input->amount);

        $transaction = new DepositTransaction(
            new TransactionId(TransactionId::genereateId()),
            $input->amount,
            $input->comment,
            $input->userId,
            new \DateTimeImmutable()
        );


        $this->transactionManager->startTransaction();

        try {

            $this->balanceRepository->save($balance);

            $this->transactionRepository->save($transaction);

            $this->transactionManager->commitTransaction();

        } catch (\Throwable $e) {
            $this->transactionManager->rollbackTransaction();
            throw new TransactionFailedException('Withdraw failed: ' . $e->getMessage(), 0, $e);
        }

        return new TransactionResultDto(
            $transaction->getUserId()->getValue(),
            $transaction->getType()->value,
            $transaction->getAmount()->getValue(),
            $transaction->getComment()
        );
    }
}
