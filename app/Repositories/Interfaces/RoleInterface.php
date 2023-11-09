<?php

namespace App\Repositories\Interfaces;

use App\DTO\RoleDTO;

interface RoleInterface
{
    public function findByRole(RoleDTO $roleDTO);
}
