<?php

namespace App\Providers;

use App\Repositories\HostReservationsRepository;
use App\Repositories\Interfaces\HostReservationsInterface;
use App\Repositories\Interfaces\OfficeImagesInterface;
use App\Repositories\Interfaces\OfficesInterface;
use App\Repositories\Interfaces\UserReservationsInterface;
use App\Repositories\OfficeImagesRepository;
use App\Repositories\OfficesRepository;
use App\Repositories\UserReservationsRepository;
use Illuminate\Support\ServiceProvider;

class MyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind(UserReservationsInterface::class, UserReservationsRepository::class);
        app()->bind(HostReservationsInterface::class, HostReservationsRepository::class);
        app()->bind(OfficesInterface::class, OfficesRepository::class);
        app()->bind(OfficeImagesInterface::class,OfficeImagesRepository::class );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
