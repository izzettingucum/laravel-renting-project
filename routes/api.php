<?php

use App\Http\Controllers\Renting\HostReservationController;
use App\Http\Controllers\Renting\OfficeController;
use App\Http\Controllers\Renting\OfficeImageController;
use App\Http\Controllers\Renting\TagController;
use App\Http\Controllers\Renting\UserReservationController;
use Illuminate\Http\Request;
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

// Tags...
Route::group(["prefix" => "tags", "controller" => TagController::class, "as" => "tags."], function () {
    Route::get("/", TagController::class)->name("list");
});


// Offices...
Route::group(["prefix" => "offices", "controller" => OfficeController::class, "as" => "offices."], function () {
    Route::get('/', "index")->name("list");
    Route::get('/{office}',  "show")->name("show");
    Route::group(["middleware" => ["auth:sanctum", "verified"]], function () {
        Route::post('/', "create")->name("create");
        Route::patch('/{office}', "update")->name("update");
        Route::delete('/{office}', "delete")->name("delete");
    });
});

// Office Photos...
Route::group(["prefix" => "offices/{office}/images", "controller" => OfficeImageController::class, "as" =>"offices.images."], function () {
    Route::group(["middleware" => ["auth:sanctum", "verified"]], function () {
        Route::post("/", "store")->name("create");
        Route::delete("/{image:id}", "delete")->name("delete");
    });
});

// User Reservations...
Route::group(["prefix" => "reservations", "controller" => UserReservationController::class, "as" => "reservations."], function () {
    Route::group(["middleware" => ["auth:sanctum", "verified"]], function () {
        Route::get("/", "index")->name("list");
        Route::post("/", "create")->name("create");
    });
});

// Host Reservations...
Route::group(["prefix" => "host/reservations", "controller" => HostReservationController::class, "as" => "host.reservations."], function () {
    Route::group(["middleware" => ["auth:sanctum", "verified"]], function () {
        Route::get('', "index")->name("list");
    });
});

