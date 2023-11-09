<?php

namespace App\Repositories;

use App\DTO\Auth\VerificationDTO;
use App\Models\User;
use App\Models\UserVerificationMail;

class VerificationRepository implements Interfaces\VerificationInterface
{
    public $userVerificationModel;

    public function __construct(UserVerificationMail $userVerificationModel)
    {
        $this->userVerificationModel = $userVerificationModel;
    }

    public function create(User $user, VerificationDTO $verificationDTO)
    {
        $user->verificationMails()->create([
            "hash" => $verificationDTO->hash
        ]);
    }

    public function controlByHash(VerificationDTO $verificationDTO)
    {
        return $this->userVerificationModel->where("hash", $verificationDTO->hash)->exists();
    }

    public function deleteVerificationMails(User $user)
    {
        $user->verificationMails()->delete();
    }
}
