<?php

namespace App\Services\AuthServices;

use App\DTO\Auth\UserDTO;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPasswordService
{
    protected $userRepository, $userDTO;

    public function __construct(UserRepository $userRepository, UserDTO $userDTO)
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
