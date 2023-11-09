<?php

namespace App\Repositories\Interfaces;

use App\DTO\Auth\VerificationDTO;
use App\Models\User;

interface VerificationInterface
{
    public function create(User $user, VerificationDTO $verificationDTO);
    public function controlByHash(VerificationDTO $verificationDTO);
    public function deleteVerificationMails(User $user);
}
