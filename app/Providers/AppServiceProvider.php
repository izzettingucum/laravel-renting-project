<?php

namespace App\Providers;

use App\DTO\Auth\UserDTO;
use App\DTO\Auth\VerificationDTO;
use App\DTO\HostReservationDTO;
use App\DTO\OfficeDTO;
use App\DTO\OfficeImageDTO;
use App\DTO\ReservationDTO;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(OfficeDTO::class, function ($app) {
            return new OfficeDTO([]);
        });

        $this->app->bind(OfficeImageDTO::class, function ($app) {
            return new OfficeImageDTO([]);
        });

        $this->app->bind(ReservationDTO::class, function ($app) {
            return new ReservationDTO([]);
        });

        $this->app->bind(HostReservationDTO::class, function ($app) {
            return new HostReservationDTO([]);
        });

        $this->app->bind(UserDTO::class, function ($app) {
            return new UserDTO([]);
        });

        $this->app->bind(VerificationDTO::class, function ($app) {
            return new VerificationDTO([]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Model::unguard();

        Relation::enforceMorphMap([
            "office" => Office::class,
            "user" => User::class
        ]);
    }
}
