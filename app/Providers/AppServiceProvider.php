<?php

namespace App\Providers;

use App\Http\DTO\HostReservationDTO;
use App\Http\DTO\OfficeDTO;
use App\Http\DTO\OfficeImageDTO;
use App\Http\DTO\UserReservationDTO;
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

        $this->app->bind(UserReservationDTO::class, function ($app) {
            return new UserReservationDTO([]);
        });

        $this->app->bind(HostReservationDTO::class, function ($app) {
            return new HostReservationDTO([]);
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
