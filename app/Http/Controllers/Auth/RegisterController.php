<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Services\AuthServices\RegisterService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class RegisterController extends Controller
{
    protected $registerService, $userService;

    public function __construct(RegisterService $registerService, UserService $userService)
    {
        $this->registerService = $registerService;
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerService->registerUser($request);
        $this->userService->createRoleForUser($user, Role::ROLE_USER);
        $user = $this->userService->createTokenForUser($user);

        return response()->json([
            "user" => UserResource::make($user),
            "message" => "Hello $user->name. We have sent a verification code to your email.You can verify it to complete your registration process.If you are experiencing difficulties receiving the verification code, please press the 'Resend' button."
        ], Response::HTTP_OK);
    }
}
