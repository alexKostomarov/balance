<?php

use App\BalanceApp\Infrastructure\Providers\InfrastructureServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    InfrastructureServiceProvider::class,
    App\Providers\RouteServiceProvider::class
];
