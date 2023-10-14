<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserReservationController extends Controller
{
    public function index(ReservationRequest $request)
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
}
