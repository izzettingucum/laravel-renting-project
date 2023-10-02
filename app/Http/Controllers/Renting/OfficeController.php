<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $offices = Office::query()
            ->where("approval_status", Office::APPROVAL_APPROVED)
            ->where("hidden", false)
            ->when(request("user_id"), function ($query) {
                return $query->whereUserId(request("user_id"));
            })
            ->when(request("visitor_id"), function ($query) {
                return $query->whereRelation("reservations", "user_id", "=", request("visitor_id"));
            })
            ->when(request("lat") && request("lng"), function ($query) {
                return $query->NearestTo(request("lat"), request("lng"));
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

    public function create(OfficeRequest $request) : OfficeResource
    {
        if(! auth()->user()->tokenCan("office.create")) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $attributes = $request->validated();

        $attributes["approval_status"] = Office::APPROVAL_PENDING;

        $office = Auth()->user()->offices()->create(
            Arr::except($attributes, "tags")
        );

        $office->tags()->sync($attributes["tags"]);

        return OfficeResource::make($office);
    }
}
