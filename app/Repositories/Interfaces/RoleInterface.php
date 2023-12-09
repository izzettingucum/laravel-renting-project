<?php

namespace App\Repositories\Interfaces;

use App\DTO\Auth\RoleDTO;

interface RoleInterface
{
    public function findByRole(RoleDTO $roleDTO);
}
