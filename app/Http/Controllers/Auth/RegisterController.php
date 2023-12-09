<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Services\AuthServices\RegisterService;
use App\Services\UserService;
use App\Traits\AuthProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class RegisterController extends Controller
{
    use AuthProcess;

    protected $registerService, $userService;

    public function __construct(RegisterService $registerService, UserService $userService)
    {
        $this->registerService = $registerService;
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request);
        $this->userService->createRoleForUser($user, Role::ROLE_USER);
        $this->registerService->triggerRegisteredEvent($user);
        $user = $this->createTokenForUser($user, "api_token");

        return response()->json([
            "user" => UserResource::make($user),
            "message" => "Hello $user->name. We have sent a verification code to your email.You can verify it to complete your registration process.If you are experiencing difficulties receiving the verification code, please press the 'Resend' button."
        ], Response::HTTP_OK);
    }
}
