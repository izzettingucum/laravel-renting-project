<?php

namespace App\Repositories;

use App\DTO\Auth\UserDTO;
use App\Models\Role;
use App\Models\User;

class UserRepository implements Interfaces\UserInterface
{
    public $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function create(UserDTO $userDTO)
    {
        $user = $this->userModel->create([
            "name" => $userDTO->name,
            "email" => $userDTO->email,
            "password" => $userDTO->password
        ]);

        return $user;
    }

    public function findById(UserDTO $userDTO)
    {
        $user = $this->userModel->findOrFail($userDTO->id);

        return $user;
    }

    public function createUserRole(UserDTO $userDTO)
    {
        $user = $this->userModel->findOrFail($userDTO->id);

        $user->userRole()->create([
            "role_id" => $userDTO->roleId
        ]);
    }

    public function getAllAdmins()
    {
        $adminUsers = $this->userModel->with(['userRole.role' => function ($query) {
            $query->where("role", Role::ROLE_ADMIN);
        }])->get();

        return $adminUsers;
    }
}
