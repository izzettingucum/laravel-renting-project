<?php

namespace App\Services\ReservationServices;

use App\DTO\OfficeDTO;
use App\DTO\ReservationDTO;
use App\Http\Requests\UserReservations\CreateRequest;
use App\Http\Requests\UserReservations\IndexRequest;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\Reservations\NewHostReservation;
use App\Notifications\Reservations\NewUserReservation;
use App\Repositories\OfficeRepositories\OfficesRepository;
use App\Repositories\ReservationRepositories\UserReservationsRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserReservationService
{
    protected $userReservationsRepository, $officesRepository, $reservationDTO, $officeDTO;

    public function __construct(
        UserReservationsRepository $userReservationsRepository,
        OfficesRepository          $officesRepository,
        ReservationDTO             $reservationDTO,
        OfficeDTO                  $officeDTO
    )
    {
        $this->userReservationsRepository = $userReservationsRepository;
        $this->officesRepository = $officesRepository;
        $this->reservationDTO = $reservationDTO;
        $this->officeDTO = $officeDTO;
    }

    public function getUserReservations(IndexRequest $request)
    {
        $reservationDTO = $this->reservationDTO->create([
            "userId" => auth()->id(),
            "officeId" => $request->office_id,
            "status" => $request->status,
            "fromDate" => $request->from_date,
            "toDate" => $request->to_date,
            "perPage" => 20
        ]);

        $reservations = $this->userReservationsRepository->getUserReservations($reservationDTO);

        return $reservations;
    }

    public function makeReservation(Office $office, CreateRequest $request)
    {
        throw_if(
            $office->user_id == auth()->id(),
            ValidationException::withMessages(["office_id" => "you cannot make a reservation on your own office"])
        );

        throw_if(
            $office->hidden == true || $office->approval_status == Office::APPROVAL_PENDING,
            ValidationException::withMessages(["office_id" => "you cannot make a reservation on a hidden office"])
        );

        $reservation = Cache::lock("reservations_office_" . $office->id, 10)->block(3, function () use ($office, $request) {
            $numberOfDays = Carbon::parse($request->end_date)->endOfDay()->diffInDays(
                Carbon::parse($request->start_date)->startOfDay()
            );

            throw_if(
                $numberOfDays < 2,
                ValidationException::withMessages(["start_date" => "you cannot make a reservation for only 1 day"])
            );

            throw_if(
                $office->reservations()->ActiveBetween($request->start_date, $request->end_date)->exists(),
                ValidationException::withMessages(["start_date" => "you cannot make a reservation during this time"])
            );

            $price = $numberOfDays * $office->price_per_day;

            if ($numberOfDays >= 28 && $office->monthly_discount != 0) {
                $price = $price - ($price * $office->monthly_discount / 100);
            }

            $reservationDTO = $this->reservationDTO->create([
                "userId" => auth()->id(),
                "officeId" => $office->id,
                "price" => $price,
                "status" => Reservation::STATUS_ACTIVE,
                "wifi_password" => Str::random(),
                "startDate" => $request->start_date,
                "endDate" => $request->end_date
            ]);

            $reservation = $this->userReservationsRepository->store($reservationDTO);

            return $reservation;
        });

        return $reservation;
    }

    public function cancel($id)
    {
        $this->reservationDTO->setId($id);

        $reservation = $this->userReservationsRepository->findById($this->reservationDTO);

        throw_if(
            auth()->id() != $reservation->user_id ||
            $reservation->status == Reservation::STATUS_CANCELLED ||
            $reservation->start_date < now()->toDateString(),
            ValidationException::withMessages(["reservation" => "you cannot cancel this reservation."])
        );

        $this->reservationDTO->setStatus(Reservation::STATUS_CANCELLED);

        $reservation = $this->userReservationsRepository->updateStatus($this->reservationDTO);

        return $reservation;
    }

    public function sendNewUserReservationNotification(User $user, Reservation $reservation)
    {
        Notification::send($user, new NewUserReservation($reservation));
    }
}
