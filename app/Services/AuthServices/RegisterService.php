<?php

namespace App\Services\AuthServices;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

class RegisterService
{
    public function triggerRegisteredEvent(User $user)
    {
        event(new Registered($user));
    }
}
