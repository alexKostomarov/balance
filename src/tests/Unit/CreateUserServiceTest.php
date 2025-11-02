<?php

namespace Tests\Unit;

use App\BalanceApp\Application\Service\CreateUserService;
use App\BalanceApp\Application\UseCaseInput\CreateUserInput;
use App\BalanceApp\Domain\User\User;
use App\BalanceApp\Domain\User\UserId;
use App\BalanceApp\Domain\User\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CreateUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_created_successfully()
    {
        $mockRepo = $this->createMock(UserRepositoryInterface::class);

        $mockRepo->expects($this->once())->method('save')
            ->with($this->callback(function ($user) {
                return $user instanceof User &&
                    $user->getName() === 'user' &&
                    $user->getEmail() === 'user@email.com';
            }));

        $service = new CreateUserService($mockRepo);

        $user = $service->create(new CreateUserInput('user','user@email.com'));

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals('user', $user->getName());
        $this->assertEquals('user@email.com', $user->getEmail());
    }


}
