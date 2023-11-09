<?php

namespace App\Services;

use App\DTO\Auth\UserDTO;
use App\DTO\RoleDTO;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected $userRepository, $userDTO, $roleRepository, $roleDTO;

    public function __construct(UserRepository $userRepository, UserDTO $userDTO, RoleRepository $roleRepository, RoleDTO $roleDTO)
    {
        $this->userRepository = $userRepository;
        $this->userDTO = $userDTO;
        $this->roleRepository = $roleRepository;
        $this->roleDTO = $roleDTO;
    }

    public function createRoleForUser(User $user, $role)
    {
        throw_if(
            $user->userRole()->exists(),
            ValidationException::withMessages(["current user has a role!"])
        );

        $this->roleDTO->setRole($role);
        $role = $this->roleRepository->findByRole($this->roleDTO);

        $this->userDTO->setId($user->id);
        $this->userDTO->setRoleId($role->id);

        $this->userRepository->createUserRole($this->userDTO);
    }

    public function createTokenForUser(User $user): User
    {
        throw_if(
            ! $user->userRole()->exists(),
            ValidationException::withMessages(["user doesnt have a role"])
        );

        $permissions = $user->userRole->role->permissions;

        $user["token"] = $user->createToken("api_token", $permissions->pluck("name")->toArray())->plainTextToken;

        return $user;
    }
}
