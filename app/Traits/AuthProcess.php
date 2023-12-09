<?php

namespace App\Traits;

use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Validation\ValidationException;

trait AuthProcess
{
    public function createTokenForUser(User $user, $name): User
    {
        throw_if(
            ! $user->userRole()->exists(),
            ValidationException::withMessages(["user doesnt have a role"])
        );

        $permissions = $user->userRole->role->permissions;

        $user["token"] = $user->createToken("$name", $permissions->pluck("name")->toArray())->plainTextToken;

        return $user;
    }

    public function makeTwoFactorCode(): int
    {
        $two_factor_code = rand(100000, 1000000);

        $controlCode = TwoFactorCode::where("code", $two_factor_code)->exists();

        if ($controlCode) {
            $this->makeTwoFactorCode();
        }
        else {
            return $two_factor_code;
        }
    }
}
