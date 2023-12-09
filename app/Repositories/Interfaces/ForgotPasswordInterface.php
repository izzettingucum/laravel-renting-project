<?php

namespace App\Repositories\Interfaces;

use App\DTO\Auth\ForgotPasswordDTO;
use App\Models\User;

interface ForgotPasswordInterface
{
    public function create(User $user, ForgotPasswordDTO $forgotPasswordDTO);

    public function findRecoveryCodeByUser(User $user);

    public function controlUserCode(User $user);

    public function deleteUserCode(User $user);
}
