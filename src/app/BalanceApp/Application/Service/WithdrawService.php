<?php

namespace App\BalanceApp\Application\Service;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Application\Dto\TransactionResultDto;
use App\BalanceApp\Application\Exception\BalanceNotFoundException;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\UseCaseInput\WithdrawInput;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\Transaction\Transactions\WithdrawTransaction;

final readonly class WithdrawService
{

    public function __construct(
        private BalanceRepositoryInterface     $balanceRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private TransactionManagerInterface    $transactionManager
    ) {}

    public function withdraw(WithdrawInput $input): TransactionResultDto
    {

        $balance = $this->balanceRepository->getByUserId($input->userId);

        if(!$balance){
            throw new BalanceNotFoundException('Баланс не найден');
        }

        $balance->withdraw($input->amount);

        $transaction = new WithdrawTransaction(
            new TransactionId(TransactionId::genereateId()),
            $input->amount,
            $input->comment,
            $input->userId,
            new \DateTimeImmutable()
        );

        try{
            $this->balanceRepository->save($balance);
            $this->transactionRepository->save($transaction);
        }
        catch (\Throwable $e){
            $this->transactionManager->rollbackTransaction();
            throw new TransactionFailedException('Withdraw failed: ' . $e->getMessage(), 0, $e);
        }


        return new TransactionResultDto(
            $input->userId->getValue(),
            $transaction->getType()->value,
            $transaction->getAmount()->getAmount(),
            $transaction->getComment()
        );
    }
}
