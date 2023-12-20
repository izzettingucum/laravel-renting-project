<?php

namespace App\Services\AuthServices;

use App\DTO\Auth\UserDTO;
use App\Models\User;
use App\Repositories\Interfaces\UserInterface;
use Illuminate\Support\Facades\Hash;

class ResetPasswordService
{
    protected $userRepository, $userDTO;

    public function __construct(UserInterface $userRepository, UserDTO $userDTO)
    {
        $this->userRepository = $userRepository;
        $this->userDTO = $userDTO;
    }

    public function resetPassword($user, $request): User
    {
        $this->userDTO->setPassword(Hash::make($request->password));

        $user = $this->userRepository->updateUserPassword($user, $this->userDTO);

        return $user;
    }
}
