<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserReservation\CreateRequest;
use App\Http\Requests\UserReservation\IndexRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class UserReservationController extends Controller
{
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        abort_unless(auth()->user()->tokenCan("reservations.show"),
            Response::HTTP_FORBIDDEN
        );

        $reservations = Reservation::query()
            ->where("user_id", auth()->id())
            ->when(request("office_id"),
            function ($query) {
                return $query->where("office_id", request("office_id"));
                }
            )->when(request("status"),
            function ($query) {
                return $query->where("status", request("status"));
                }
            )->when(request('from_date') && request('to_date'),
                function ($query) {
                    return $query->betweenDates(request('from_date'), request('to_date'));
                }
            )->with(["office.featuredImage"])
            ->paginate(20);

        return ReservationResource::collection(
            $reservations
        );
    }

    public function create(CreateRequest $request)
    {
        abort_unless(auth()->user()->tokenCan("reservations.make"),
            Response::HTTP_FORBIDDEN
        );

        try {
            $office = Office::findOrFail($request->office_id);
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

            return Reservation::create([
                "user_id" => auth()->id(),
                "office_id" => $office->id,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "status" => Reservation::STATUS_ACTIVE,
                "price" => $price
            ]);
        });

        return ReservationResource::make(
            $reservation->load("office")
        );
    }
}
