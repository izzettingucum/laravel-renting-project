<?php

namespace App\Repositories;

use App\DTO\RoleDTO;
use App\Models\Role;

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
