<?php

namespace App\Http\Controllers\Api;

use App\BalanceApp\Application\Service\DepositService;
use App\BalanceApp\Application\Service\TransferService;
use App\BalanceApp\Application\Service\WithdrawService;
use App\BalanceApp\Application\UseCaseInput\DepositInput;
use App\BalanceApp\Application\UseCaseInput\TransferInput;
use App\BalanceApp\Application\UseCaseInput\WithdrawInput;
use App\BalanceApp\Domain\Money\Money;
use App\BalanceApp\Domain\User\UserId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Операции с балансом: пополнение, вывод, перевод"
 * )
 */
class TransactionController extends Controller
{
    public function __construct(
        private readonly DepositService $depositService,
        private readonly WithdrawService $withdrawService,
        private readonly TransferService $transferService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/deposit",
     *     summary="Пополнение счёта",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "amount"},
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="comment", type="string", example="Пополнение через терминал")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Депозит выполнен", @OA\JsonContent(
     *         @OA\Property(property="transaction_id", type="string", format="uuid")
     *     )),
     *     @OA\Response(response=403, description="Несовпадение X-User-ID и user_id"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function deposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'amount' => 'required|numeric|min:1',
            'comment' => 'nullable|string|max:255',
        ]);


        $input = new DepositInput(
            new UserId($validated['user_id']),
            new Money($validated['amount']),
            $validated['comment'] ?? null
        );



        $resultDto = $this->depositService->deposit($input);

        return response()->json($resultDto);
    }

    /**
     * @OA\Post(
     *     path="/api/withdraw",
     *     summary="Списание средств",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "amount"},
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="comment", type="string", example="Снятие наличных")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Вывод выполнен", @OA\JsonContent(
     *         @OA\Property(property="transaction_id", type="string", format="uuid")
     *     )),
     *     @OA\Response(response=403, description="Несовпадение X-User-ID и user_id"),
     *     @OA\Response(response=400, description="Недостаточно средств"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'amount' => 'required|numeric|min:1',
            'comment' => 'nullable|string|max:255',
        ]);


        $input = new WithdrawInput(
            new UserId($validated['user_id']),
            new Money($validated['amount']),
            $validated['comment'] ?? null
        );

        $resultDto = $this->withdrawService->withdraw($input);

        return response()->json($resultDto);
    }

    /**
     * @OA\Post(
     *     path="/api/transfer",
     *     summary="Перевод между пользователями",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from_user_id", "to_user_id", "amount"},
     *             @OA\Property(property="from_user_id", type="string", format="uuid"),
     *             @OA\Property(property="to_user_id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="comment", type="string", example="Перевод за обед")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Перевод выполнен", @OA\JsonContent(
     *         @OA\Property(property="transaction_id", type="string", format="uuid")
     *     )),
     *     @OA\Response(response=403, description="Несовпадение X-User-ID и from_user_id"),
     *     @OA\Response(response=400, description="Недостаточно средств"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function transfer(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'to_user_id' => 'required|uuid',
            'from_user_id' => 'required|uuid',
            'amount' => 'required|numeric|min:1',
            'comment' => 'nullable|string|max:255',
        ]);

        $input = new TransferInput(
            new UserId($validated['from_user_id']),
            new UserId($validated['to_user_id']),
            new Money($validated['amount']),
            $validated['comment'] ?? null
        );

        $resultDto = $this->transferService->transfer($input);

        return response()->json([
            'status' => 'success',
            'data' => $resultDto,
        ], 200);


    }
}
