<?php

namespace App\Services\AuthServices;

use App\DTO\Auth\ForgotPasswordDTO;
use App\DTO\Auth\UserDTO;
use App\Models\User;
use App\Notifications\Auth\ForgotPasswordNotification;
use App\Repositories\Interfaces\ForgotPasswordInterface;
use App\Repositories\Interfaces\UserInterface;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordService
{
    protected $userRepository, $userDTO, $forgotPasswordRepository, $forgotPasswordDTO;

    public function __construct(UserInterface $userRepository, UserDTO $userDTO, ForgotPasswordInterface $forgotPasswordRepository, ForgotPasswordDTO $forgotPasswordDTO)
    {
        $this->userRepository = $userRepository;
        $this->userDTO = $userDTO;
        $this->forgotPasswordRepository = $forgotPasswordRepository;
        $this->forgotPasswordDTO = $forgotPasswordDTO;
    }

    public function sendRecoveryCode($request)
    {
        $this->userDTO->setEmail($request->email);

        $user = $this->userRepository->findByEmail($this->userDTO);

        $forgotMailCheck = $this->forgotPasswordRepository->controlUserCode($user);

        if ($forgotMailCheck) {
            $this->forgotPasswordRepository->deleteUserCode($user);
        }

        $code = rand(100000, 1000000);

        $forgotPasswordDTO = $this->forgotPasswordDTO->create([
            "code" => $code,
            "rememberToken" => Str::random(8),
            "expiredAt" => now()->addMinute(5)
        ]);

        $this->forgotPasswordRepository->create($user, $forgotPasswordDTO);

        Notification::send($user, new ForgotPasswordNotification($code));
    }

    public function checkRecoveryCode($request)
    {
        $this->userDTO->setEmail($request->email);

        $user = $this->userRepository->findByEmail($this->userDTO);

        $recoveryCode = $this->forgotPasswordRepository->findRecoveryCodeByUser($user);

        throw_if(
            ! $recoveryCode ||
            $recoveryCode->code != $request->code ||
            $recoveryCode->expired_at < now()->subMinute(5),
            ValidationException::withMessages([['message' => 'Invalid or expired token']])
        );

        return $recoveryCode;
    }

    public function checkRememberToken(User $user, $request)
    {
        $recoveryCode = $this->forgotPasswordRepository->findRecoveryCodeByUser($user);

        throw_if(
            $recoveryCode->remember_token != $request->remember_token,
            ValidationException::withMessages(["invalid remember token"])
        );
    }

    public function deleteRecoveryCode(User $user)
    {
        $this->forgotPasswordRepository->deleteUserCode($user);
    }
}
