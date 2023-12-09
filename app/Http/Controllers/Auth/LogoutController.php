<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthServices\LogoutService;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    protected $logoutService;

    public function __construct(LogoutService $logoutService)
    {
        $this->logoutService = $logoutService;
    }

    public function logout(Request $request)
    {
        $this->logoutService->logout($request);

        return response()->json([
            "message" => $this->logoutService->setLogoutMessage()
        ]);
    }
}
