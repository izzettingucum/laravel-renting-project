<?php

namespace App\Repositories\AuthRepositories;

use App\DTO\TwoFactorDTO;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Repositories\Interfaces\TwoFactorInterface;
use Illuminate\Database\Eloquent\Model;

class TwoFactorRepository implements TwoFactorInterface
{
    public $twoFactorModel;

    public function __construct(TwoFactorCode $twoFactorModel)
    {
        $this->twoFactorModel = $twoFactorModel;
    }

    public function findCurrentUserCode(TwoFactorDTO $twoFactorDTO)
    {
        $twoFactorCode = $this->twoFactorModel->query()
            ->where("user_id", $twoFactorDTO->userId)
            ->where("code", $twoFactorDTO->code)
            ->first();

        return $twoFactorCode;
    }

    public function controlCurrentUserCode(TwoFactorDTO $twoFactorDTO)
    {
        $twoFactorCode = $this->twoFactorModel->where("code", $twoFactorDTO->code)->exists();

        return $twoFactorCode;
    }

    public function findByUserId(TwoFactorDTO $twoFactorDTO)
    {
        $twoFactorCode = $this->twoFactorModel->where("user_id", $twoFactorDTO->user_id);

        return $twoFactorCode;
    }

    public function createCode(User $user, TwoFactorDTO $twoFactorDTO): Model
    {
        $twoFactorCode = $user->twoFactorCode()->create([
            "code" => $twoFactorDTO->code,
            "expires_at" => $twoFactorDTO->expiresAt
        ]);

        return $twoFactorCode;
    }
}
