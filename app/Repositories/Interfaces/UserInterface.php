<?php

namespace App\Repositories\Interfaces;

use App\DTO\Auth\UserDTO;
use App\Models\User;

interface UserInterface
{
    public function create(UserDTO $userDTO);

    public function findById(UserDTO $userDTO);

    public function findByEmail(UserDTO $userDTO);

    public function createUserRole(UserDTO $userDTO);

    public function getAllAdmins();

    public function updateUserPassword(User $user, UserDTO $userDTO);
}
