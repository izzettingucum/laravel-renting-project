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
            $this->message = "Başarıyla çıkış yaptınız";
        }
        else {
            $this->message = "Çıkış işlemi başarısız";
        }

        return $this->message;
    }
}
