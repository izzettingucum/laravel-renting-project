<?php

namespace App\Services\AuthServices;

use App\DTO\Auth\UserDTO;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public $userRepository, $userDTO;

    public function __construct(UserRepository $userRepository, UserDTO $userDTO)
    {
        $this->userRepository = $userRepository;
        $this->userDTO = $userDTO;
    }

    public function registerUser(RegisterRequest $request)
    {
        $userDTO = $this->userDTO->create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        $user = $this->userRepository->create($userDTO);

        event(new Registered($user));

        return $user;
    }
}
