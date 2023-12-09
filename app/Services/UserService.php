<?php

namespace App\Services;

use App\DTO\Auth\RoleDTO;
use App\DTO\Auth\UserDTO;
use App\Models\User;
use App\Repositories\AuthRepositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
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

    public function createUser($attributes)
    {
        $userDTO = $this->userDTO->create([
            "name" => $attributes->name,
            "email" => $attributes->email,
            "password" => Hash::make($attributes->password)
        ]);

        $user = $this->userRepository->create($userDTO);

        return $user;
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

    public function getUserByEmail($email)
    {
        $this->userDTO->setEmail($email);

        $user = $this->userRepository->findByEmail($this->userDTO);

        return $user;
    }

    public function controlUserPassword($password, $userPassword)
    {
        throw_if(
            ! Hash::check($password, $userPassword),
            ValidationException::withMessages(["your current password is invalid."])
        );
    }

    public function validateUser($user)
    {
        throw_if(
            ! $user,
            ValidationException::withMessages(["message" => "invalid user."])
        );
    }
}
