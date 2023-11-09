<?php

namespace App\Repositories\Interfaces;

use App\DTO\Auth\UserDTO;

interface UserInterface
{
    public function create(UserDTO $userDTO);

    public function findById(UserDTO $userDTO);

    public function createUserRole(UserDTO $userDTO);

    public function getAllAdmins();
}
