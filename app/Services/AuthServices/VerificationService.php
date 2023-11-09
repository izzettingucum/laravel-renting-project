<?php

namespace App\Services\AuthServices;

use App\DTO\Auth\VerificationDTO;
use App\Repositories\VerificationRepository;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    public $verificationDTO, $verificationRepository;

    public function __construct(VerificationDTO $verificationDTO, VerificationRepository $verificationRepository)
    {
        $this->verificationDTO = $verificationDTO;
        $this->verificationRepository = $verificationRepository;
    }

    public function verifyUserEmail(EmailVerificationRequest $request)
    {
        throw_if(
            $request->id != auth()->id(),
            ValidationException::withMessages(["error" => "invalid user id"])
        );

        $this->verificationDTO->setHash(sha1(auth()->user()->getEmailForVerification()));

        throw_if(
            ! $this->verificationRepository->controlByHash($this->verificationDTO),
            ValidationException::withMessages(["error" => "invalid hash"])
        );

        $this->verificationRepository->deleteVerificationMails(auth()->user());

        $request->fulfill();
    }

    public function resendVerificationNotification(Request $request)
    {
        $user = $request->user();

        throw_if(
            $user->hasVerifiedEmail(),
            ValidationException::withMessages(["error" => "Your email is already verified!"])
        );

        $user->sendEmailVerificationNotification();
    }
}
