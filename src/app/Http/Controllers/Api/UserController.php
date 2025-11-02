<?php

namespace App\Http\Controllers\Api;

use App\BalanceApp\Application\Service\CreateUserService;
use App\BalanceApp\Application\Service\ListUsersService;
use App\BalanceApp\Application\UseCaseInput\CreateUserInput;
use App\BalanceApp\Domain\User\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserService $service,
        private readonly ListUsersService $listUsers
    ) {}

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Получить список всех пользователей",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Список пользователей",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="e7b8c2f0-1234-4a9f-8a2b-abc123def456"),
     *                 @OA\Property(property="name", type="string", example="Name"),
     *                 @OA\Property(property="email", type="string", example="name@example.com")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $users = $this->listUsers->list();


        return response()->json(array_map(fn(User $user) => [
            'id' => $user->getId()->getValue(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ], $users));
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Создать нового пользователя",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="Name"),
     *             @OA\Property(property="email", type="string", format="email", example="name@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="e7b8c2f0-1234-4a9f-8a2b-abc123def456"),
     *             @OA\Property(property="message", type="string", example="User created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Email уже занят",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User with this email already exists")
     *         )
     *     )
     * )
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $input = new CreateUserInput($validated['name'], $validated['email']);

        try {

            $user = $this->service->create($input);

            return response()->json([
                'id' => $user->getId()->getValue(),
                'message' => 'User created successfully',
            ], 201);

        } catch (\RuntimeException $e) {

            return response()->json([
                'error' => $e->getMessage(),
            ], 409);
        }
    }
}
