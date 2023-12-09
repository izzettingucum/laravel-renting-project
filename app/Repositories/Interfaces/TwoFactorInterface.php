<?php

namespace App\Repositories\Interfaces;

use App\DTO\TwoFactorDTO;
use App\Models\User;

interface TwoFactorInterface
{
    public function findCurrentUserCode(TwoFactorDTO $twoFactorDTO);

    public function controlCurrentUserCode(TwoFactorDTO $twoFactorDTO);

    public function findByUserId(TwoFactorDTO $twoFactorDTO);

    public function createCode(User $user, TwoFactorDTO $twoFactorDTO);
}
