<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Offices\OfficeController;
use App\Http\Controllers\Offices\OfficeImageController;
use App\Http\Controllers\Reservations\HostReservationController;
use App\Http\Controllers\Reservations\UserReservationController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// AuthProcess...
Route::group(["prefix" => "auth"], function () {
    Route::group(["middleware" => ["guest"]], function () {
        Route::post("/register", [RegisterController::class, "register"])->name("register");
        Route::post("/login", [LoginController::class, "login"])->name("login");
        Route::post("/login/twoFactor", [LoginController::class, "loginWithTwoFactor"])->name("login.twoFactor");
        Route::group(["prefix" => "password"], function () {
            Route::post("/reset", [ResetPasswordController::class, "resetPassword"])->name("reset.password");
            Route::post("/forgot", [ForgotPasswordController::class, "sendRecoveryCode"])->name("send.recovery.code");
            Route::post("/forgot/check/code", [ForgotPasswordController::class, "checkRecoveryCode"])->name("check.recovery.code");
            Route::post("/forgot/reset", [ForgotPasswordController::class, "resetPassword"])->name("forgot.reset.password");
        });
    });

    Route::group(["middleware" => ["auth"]], function () {
        Route::post("/reset/password", [ResetPasswordController::class, "resetPassword"])->name("reset.password");
        Route::post("/logout", [LogoutController::class, "logout"])->name("logout");
    });
});

// Offices...
Route::group(["prefix" => "offices", "controller" => OfficeController::class, "as" => "offices."], function () {
    Route::get('/', "index")->name("list");
    Route::get('/{id}', "show")->name("show");
    Route::group(["middleware" => ["auth:sanctum", "verified", "authorize"]], function () {
        Route::post('/', "create")->name("create");
        Route::patch('/{id}', "update")->name("update");
        Route::delete('/{id}', "delete")->name("delete");
    });
});

// Office Photos...
Route::group(["prefix" => "offices/{office}/images", "controller" => OfficeImageController::class, "as" => "offices.images."], function () {
    Route::group(["middleware" => ["auth:sanctum", "verified", "authorize"]], function () {
        Route::post("", "store")->name("create");
        Route::delete("/{image:id}", "delete")->name("delete");
    });
});

// User Reservations...
Route::group(["prefix" => "reservations", "controller" => UserReservationController::class, "as" => "reservations."], function () {
    Route::group(["middleware" => ["auth:sanctum", "verified", "authorize"]], function () {
        Route::get("/", "index")->name("list");
        Route::post("/", "create")->name("create");
        Route::delete("/{id}", "cancel")->name("cancel");
    });
});

// Host Reservations...
Route::group(["prefix" => "host/reservations", "controller" => HostReservationController::class, "as" => "host.reservations."], function () {
    Route::group(["middleware" => ["auth:sanctum", "verified", "authorize"]], function () {
        Route::get('', "index")->name("list");
    });
});

// Email Verification...
Route::group(["prefix" => "email", "controller" => VerificationController::class, "as" => "verification."], function () {
    Route::get("/verify/{id}/{hash}", "verify")->middleware(['auth', 'signed'])->name('verify');
    Route::post("/verification-notification", "resendVerificationNotification")->middleware(['auth', 'throttle:6,1'])->name('send');
});



