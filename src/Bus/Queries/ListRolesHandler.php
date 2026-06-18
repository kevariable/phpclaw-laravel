<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Queries;

use Kevariable\PhpclawLaravel\Contracts\Handler;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;

readonly class ListRolesHandler implements Handler
{
    public function __construct(protected RoleRouter $router) {}

    public function handle(object $message): array
    {
        return $this->router->all();
    }
}
