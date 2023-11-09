<?php

namespace App\Services\AuthServices;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class LogoutService
{
    public function logout(Request $request)
    {
        try {
            if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
                Auth::guard('web')->logout();

                request()->session()->invalidate();

                request()->session()->regenerateToken();
            }
            else {
                $request->user()->currentAccessToken()->delete();
            }

            return ['message' => 'Başarıyla çıkış yaptınız.'];
        }
        catch (\Exception $e) {
            return ["message" => "Çıkış yaparken bir hata oluştu : " . $e->getMessage()];
        }

    }
}
