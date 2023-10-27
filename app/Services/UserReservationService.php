<?php

namespace App\Services;

use App\Http\DTO\OfficeDTO;
use App\Http\DTO\UserReservationDTO;
use App\Http\Requests\UserReservations\CreateRequest;
use App\Http\Requests\UserReservations\IndexRequest;
use App\Models\Office;
use App\Models\Reservation;
use App\Notifications\Reservations\NewHostReservation;
use App\Notifications\Reservations\NewUserReservation;
use App\Repositories\OfficesRepository;
use App\Repositories\UserReservationsRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserReservationService
{
    protected $userReservationsRepository, $officesRepository, $userReservationDTO, $officeDTO;

    public function __construct(
        UserReservationsRepository $userReservationsRepository,
        OfficesRepository $officesRepository,
        UserReservationDTO $userReservationDTO,
        OfficeDTO $officeDTO
    )
    {
        $this->userReservationsRepository = $userReservationsRepository;
        $this->officesRepository = $officesRepository;
        $this->userReservationDTO = $userReservationDTO;
        $this->officeDTO = $officeDTO;
    }

    public function index(IndexRequest $request)
    {
        abort_unless(auth()->user()->tokenCan("reservations.show"),
            Response::HTTP_FORBIDDEN
        );

        $userReservationDTO = new $this->userReservationDTO([
            "userId" => auth()->id(),
            "officeId" => $request->office_id,
            "status" => $request->status,
            "fromDate" => $request->from_date,
            "toDate" => $request->to_date,
            "perPage" => 20
        ]);

        $reservations = $this->userReservationsRepository->getUserReservations($userReservationDTO);

        return $reservations;
    }

    public function create(CreateRequest $request)
    {

        abort_unless(auth()->user()->tokenCan("reservations.make"),
            Response::HTTP_FORBIDDEN
        );

        try {
            $officeDTO = new $this->officeDTO(["id" => $request->office_id]);
            $office = $this->officesRepository->findById($officeDTO);
        } catch (ModelNotFoundException $e) {
            throw ValidationException::withMessages([
                "office_id" => "Invalid office_id"
            ]);
        }

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

            $userReservationDTO = new $this->userReservationDTO([
                "userId" => auth()->id(),
                "officeId" => $office->id,
                "price" => $price,
                "status" => Reservation::STATUS_ACTIVE,
                "wifi_password" => Str::random(),
                "startDate" => $request->start_date,
                "endDate" => $request->end_date
            ]);

            $reservation = $this->userReservationsRepository->store($userReservationDTO);

            return $reservation;
        });

        Notification::send(auth()->user(), new NewUserReservation($reservation));
        Notification::send($office->user, new NewHostReservation($reservation));

        return $reservation;
    }

    public function cancel($id)
    {
        abort_unless(auth()->user()->tokenCan("reservations.make"),
            Response::HTTP_FORBIDDEN
        );

        $userReservationDTO = new $this->userReservationDTO(["id" => $id]);

        $reservation = $this->userReservationsRepository->findById($userReservationDTO);

        throw_if(
            auth()->id() != $reservation->user_id ||
            $reservation->status == Reservation::STATUS_CANCELLED ||
            $reservation->start_date < now()->toDateString(),
            ValidationException::withMessages(["reservation" => "you cannot cancel this reservation."])
        );

        $userReservationDTO->status = Reservation::STATUS_CANCELLED;

        $reservation = $this->userReservationsRepository->updateStatus($userReservationDTO);

        Notification::send(auth()->user(), new NewUserReservation($reservation));
        Notification::send($reservation->office->user, new NewHostReservation($reservation));

        return $reservation;
    }
}
