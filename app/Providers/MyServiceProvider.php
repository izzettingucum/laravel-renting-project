<?php

namespace App\Providers;

use App\Repositories\AuthRepositories\ForgotPasswordRepository;
use App\Repositories\AuthRepositories\TwoFactorRepository;
use App\Repositories\AuthRepositories\VerificationRepository;
use App\Repositories\Interfaces\ForgotPasswordInterface;
use App\Repositories\Interfaces\HostReservationsInterface;
use App\Repositories\Interfaces\OfficeImagesInterface;
use App\Repositories\Interfaces\OfficesInterface;
use App\Repositories\Interfaces\TwoFactorInterface;
use App\Repositories\Interfaces\UserReservationsInterface;
use App\Repositories\Interfaces\VerificationInterface;
use App\Repositories\OfficeRepositories\OfficeImagesRepository;
use App\Repositories\OfficeRepositories\OfficesRepository;
use App\Repositories\ReservationRepositories\HostReservationsRepository;
use App\Repositories\ReservationRepositories\UserReservationsRepository;
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
        app()->bind(OfficeImagesInterface::class,OfficeImagesRepository::class);
        app()->bind(VerificationInterface::class,VerificationRepository::class);
        app()->bind(TwoFactorInterface::class, TwoFactorRepository::class);
        app()->bind(ForgotPasswordInterface::class, ForgotPasswordRepository::class);
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
