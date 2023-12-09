<?php

namespace App\Services\AuthServices;

use Illuminate\Http\Request;

class LogoutService
{
    private $message;

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }

    public function setLogoutMessage()
    {
        $token = auth()->user()->currentAccessToken();

        if (! $token) {
            $this->message = "Your logout proccess completed successfully";
        }
        else {
            $this->message = "Your logout proccess couldn't complete successfully";
        }

        return $this->message;
    }
}
