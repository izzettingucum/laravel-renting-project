<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPassword\CheckResetCodeRequest;
use App\Http\Requests\Auth\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\Auth\ForgotPassword\SendResetCodeRequest;
use App\Services\AuthServices\ForgotPasswordService;
use App\Services\AuthServices\ResetPasswordService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ForgotPasswordController extends Controller
{
    protected $forgotPasswordService, $resetPasswordService, $userService;

    public function __construct(ForgotPasswordService $forgotPasswordService, ResetPasswordService $resetPasswordService, UserService $userService)
    {
        $this->forgotPasswordService = $forgotPasswordService;
        $this->resetPasswordService = $resetPasswordService;
        $this->userService = $userService;
    }

    public function sendRecoveryCode(SendResetCodeRequest $request): JsonResponse
    {
        $this->forgotPasswordService->sendRecoveryCode($request);

        return response()->json([
            "message" => "We sent a recovery code to your email. After you verify it you can reset your password successfully",
            "email" => $request->email
        ], Response::HTTP_OK);
    }

    public function checkRecoveryCode(CheckResetCodeRequest $request): JsonResponse
    {
        $recoveryCode = $this->forgotPasswordService->checkRecoveryCode($request);

        return response()->json([
            "email" => $request->email,
            "remember_token" => $recoveryCode->remember_token
        ], Response::HTTP_OK);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->userService->getUserByEmail($request->email);
        $this->userService->validateUser($user);
        $this->forgotPasswordService->checkRememberToken($user, $request);
        $this->resetPasswordService->resetPassword($user, $request);
        $this->forgotPasswordService->deleteRecoveryCode($user);

        return response()->json([
            "message" => "We reset your password successfully."
        ], Response::HTTP_OK);
    }
}
