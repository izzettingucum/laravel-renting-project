<?php

namespace App\Listeners;

use App\Events\OfficeCreated;
use App\Notifications\Offices\OfficeCreatedNotification;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendOfficeCreatedNotification
{
    public $userRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle the event.
     *
     * @param OfficeCreated $event
     * @return void
     */
    public function handle(OfficeCreated $event)
    {
        $admins = $this->userRepository->getAllAdmins();

        Notification::send($admins, new OfficeCreatedNotification($event->office));
    }
}
