<?php

namespace App\Services\AuthServices;

use App\DTO\Auth\UserDTO;
use App\DTO\TwoFactorDTO;
use App\Http\Requests\Auth\TwoFactorRequest;
use App\Models\User;
use App\Notifications\Auth\TwoFactorNotification;
use App\Repositories\AuthRepositories\TwoFactorRepository;
use App\Repositories\UserRepository;
use App\Traits\AuthProcess;
use App\Traits\TwoFactorAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class LoginService
{
    use AuthProcess;

    protected $userRepository, $userDTO, $twoFactorDTO, $twoFactorRepository, $message;

    public function __construct(UserRepository $userRepository, UserDTO $userDTO, TwoFactorDTO $twoFactorDTO, TwoFactorRepository $twoFactorRepository)
    {
        $this->userRepository = $userRepository;
        $this->userDTO = $userDTO;
        $this->twoFactorDTO = $twoFactorDTO;
        $this->twoFactorRepository = $twoFactorRepository;
    }

    public function login($request): User
    {
        $this->userDTO->setEmail($request->email);
        $user = $this->userRepository->findByEmail($this->userDTO);

        throw_if(
            ! $user || ! Hash::check($request->password, $user->password),
            ValidationException::withMessages(["message" => "Bad credentials"])
        );

        if ($user->two_factor_auth) {
            $user = $this->sendTwoFactorCode($user);
        }
        else {
            $user = $this->createTokenForUser($user, "api_token");
        }

        return $user;
    }

    public function authenticateTwoFactor(TwoFactorRequest $request): User
    {
        $userDTO = $this->userDTO->create([
            "email" => $request->email
        ]);

        $user = $this->userRepository->findByEmail($userDTO);

        $twoFactorDTO = $this->twoFactorDTO->create([
            "userId" => $user->id,
            "code" => $request->code
        ]);

        $twoFactorCode = $this->twoFactorRepository->findCurrentUserCode($twoFactorDTO);

        throw_if(
            ! $twoFactorCode,
            ValidationException::withMessages(['message' => 'Wrong code, please try again.'])
        );

        throw_if(
            $twoFactorCode->expires_at < now()->subMinute(5),
            ValidationException::withMessages(['message' => 'Your code is expired. Please take another one.'])
        );

        $user = $this->createTokenForUser($twoFactorCode->user()->first(), "api_token");

        $twoFactorCode->delete();

        return $user;
    }

    public function sendTwoFactorCode(User $user): User
    {
        if ($user->twoFactorCode()->exists()) {
            $user->twoFactorCode()->delete();
        }

        $twoFactorDTO = $this->twoFactorDTO->create([
            "code" => $this->makeTwoFactorCode(),
            "expiresAt" => now()->addMinute(5)
        ]);

        $twoFactorCode = $this->twoFactorRepository->createCode($user, $twoFactorDTO);

        Notification::send($user, new TwoFactorNotification($twoFactorCode));

        return $user;
    }

    public function setLoginMessage(User $user): string
    {
        $token = $user["token"] ?? null;

        if ($token) {
            $this->message = "Login is completed successfully.";
        }
        elseif ($user->two_factor_auth && ! $token) {
            $this->message = "we have sent authentication code to your email after you verify it you can login succesfully.";
        }
        else {
            $this->message = "You could not login successfully. Please try again";
        }

        return $this->message;
    }
}
