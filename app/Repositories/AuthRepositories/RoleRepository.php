<?php

namespace App\Repositories\AuthRepositories;

use App\DTO\Auth\RoleDTO;
use App\Models\Role;
use App\Repositories\Interfaces;

class RoleRepository implements Interfaces\RoleInterface
{

    public $roleModel;

    public function __construct(Role $roleModel)
    {
        $this->roleModel = $roleModel;
    }

    public function findByRole(RoleDTO $roleDTO)
    {
        $role = $this->roleModel->where("role", $roleDTO->role)->first();

        return $role;
    }
}
