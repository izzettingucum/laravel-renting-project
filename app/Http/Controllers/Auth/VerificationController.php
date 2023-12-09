<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthServices\VerificationService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    public $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }


    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $this->verificationService->verifyUserEmail($request);

        return response()->json([
            "message" => "Congrats! your email verified successfully"
        ], Response::HTTP_OK);
    }

    public function resendVerificationNotification(Request $request): RedirectResponse
    {
        $this->verificationService->resendVerificationNotification($request);

        return back()->with('message', 'Verification link sent!');
    }
}
