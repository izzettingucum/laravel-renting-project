<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfficeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $offices = Office::query()
            ->where("approval_status", Office::APPROVAL_APPROVED)
            ->where("hidden", false)
            ->when(request("host_id"), function ($query) {
                return $query->whereUserId(request("host_id"));
            })
            ->when(request("user_id"), function ($query) {
                return $query->whereRelation("reservations", "user_id", "=", request("user_id"));
            })
            ->when(request("lat") && request("lng"), function ($query) {
                return $query->NearestTo(request("lat"), request("lng"));
            }, function ($query) {
                return $query->orderBy("id", "ASC");
            })
            ->latest("id")
            ->with(["images", "tags", "user"])
            ->withCount(["reservations" => function ($query) {
                return $query->where("status", "=", Reservation::STATUS_ACTIVE);
            }])
            ->paginate(20);

        return OfficeResource::collection(
            $offices
        );
    }

    public function show(Office $office): OfficeResource
    {
        $office
            ->loadCount(["reservations" => function ($query) {
                return $query->where("status", "=", Reservation::STATUS_ACTIVE);
            }])
            ->load(["images", "tags", "user"]);

        return OfficeResource::make(
            $office
        );
    }
}
