<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthServices\ResetPasswordService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    protected $resetPasswordService, $userService;

    public function __construct(UserService $userService, ResetPasswordService $resetPasswordService)
    {
        $this->resetPasswordService = $resetPasswordService;
        $this->userService = $userService;
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $this->userService->validateUser($user);
        $this->userService->controlUserPassword($request->old_password, $user->password);
        $this->resetPasswordService->resetPassword($user, $request);

        return response()->json([
            "message" => "We reset your password successfully.",
        ]);
    }
}
