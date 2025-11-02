<?php


namespace App\Http\Controllers\Api;

use App\BalanceApp\Application\Service\BalanceService;
use App\BalanceApp\Domain\User\UserId;
use App\Http\Controllers\Controller;

final class BalanceController extends Controller
{
    public function __construct(
        private BalanceService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/balance/{user_id}",
     *     summary="Получить баланс пользователя",
     *     tags={"Balance"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="UUID пользователя",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Текущий баланс",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Баланс не найден")
     * )
     */
    public function show(string $user_id)
    {
        $dto = $this->service->getBalance(new UserId($user_id));

        if(!$dto){
            return response()->json(['error' => 'Balance not found'], 404);
        }

        return response()->json($dto);
    }

}
