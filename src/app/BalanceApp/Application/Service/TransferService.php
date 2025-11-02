<?php

namespace App\BalanceApp\Application\Service;

use App\BalanceApp\Application\Contract\TransactionManagerInterface;
use App\BalanceApp\Application\Dto\TransferResultDto;
use App\BalanceApp\Application\Exception\BalanceNotFoundException;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\Exception\UserNotFoundException;
use App\BalanceApp\Application\UseCaseInput\TransferInput;
use App\BalanceApp\Domain\Balance\Balance;
use App\BalanceApp\Domain\Balance\BalanceRepositoryInterface;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\Transaction\TransactionId;
use App\BalanceApp\Domain\Transaction\TransactionRepositoryInterface;
use App\BalanceApp\Domain\Transaction\Transactions\TransferTransaction;
use App\BalanceApp\Domain\Transaction\TransactionType;
use App\BalanceApp\Domain\User\UserRepositoryInterface;

final readonly class TransferService
{
    public function __construct(
        private BalanceRepositoryInterface     $balanceRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private UserRepositoryInterface        $userRepository,
        private TransactionManagerInterface    $transactionManager
    ) {}

    public function transfer(TransferInput $input): TransferResultDto
    {

        $balanceFrom = $this->balanceRepository->getByUserId($input->from);

        if(!$balanceFrom){
            throw new BalanceNotFoundException('Баланс отправителя не найден');
        }

        $userTo = $this->userRepository->findById($input->to);

        if(!$userTo){
            throw new UserNotFoundException();
        }

        $balanceTo = $this->balanceRepository->getByUserId($input->to);

        if(!$balanceTo){
            $balanceTo = new Balance( $input->to, new Money(0) );
        }

        $transactionFrom = new TransferTransaction(
            new TransactionId(TransactionId::genereateId()),
            TransactionType::TRANSFER_OUT,
            $input->amount,
            $input->comment,
            $input->from,
            $input->to,
            new \DateTimeImmutable()
        );

        $transactionTo = new TransferTransaction(
            new TransactionId(TransactionId::genereateId()),
            TransactionType::TRANSFER_IN,
            $input->amount,
            $input->comment,
            $input->to,
            $input->from,
            new \DateTimeImmutable()
        );

        $balanceFrom->withdraw($input->amount);

        $balanceTo->deposit($input->amount);

        $this->transactionManager->startTransaction();

        try {

            $this->balanceRepository->save($balanceTo);
            $this->balanceRepository->save($balanceFrom);
            $this->transactionRepository->save($transactionTo);
            $this->transactionRepository->save($transactionFrom);

            $this->transactionManager->commitTransaction();
        }
        catch (\Exception $e) {
            $this->transactionManager->rollbackTransaction();
            throw new TransactionFailedException('Transaction failed: ' . $e->getMessage(), 0, $e);
        }

        return new TransferResultDto(
            $transactionFrom->getUserId()->getValue(),
            $transactionTo->getUserId()->getValue(),
            $transactionFrom->getAmount()->getAmount(),
            $transactionFrom->getComment()
        );
    }
}
