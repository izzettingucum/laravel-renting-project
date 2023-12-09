<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\TwoFactorRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthServices\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class LoginController extends Controller
{
    public $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->loginService->login($request);
        $message = $this->loginService->setLoginMessage($user);

        return response()->json([
            "user" => UserResource::make($user),
            "message" => $message
        ], Response::HTTP_OK);
    }

    public function loginWithTwoFactor(TwoFactorRequest $request): JsonResponse
    {
        $user = $this->loginService->authenticateTwoFactor($request);
        $message = $this->loginService->setLoginMessage($user);

        return response()->json([
            "user" => UserResource::make($user),
            "message" => $message
        ], Response::HTTP_OK);
    }
}
