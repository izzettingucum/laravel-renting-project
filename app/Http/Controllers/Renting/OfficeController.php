<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        abort_unless(auth()->user()->tokenCan("office.create"),
        Response::HTTP_FORBIDDEN
        );

        $attributes = $request->validated();
        $attributes["approval_status"] = Office::APPROVAL_PENDING;

        $office = DB::transaction(function () use($attributes) {
            $office = auth()->user()->offices()->create(
                Arr::except($attributes, "tags")
            );

            if (isset($attributes["tags"])) {
                $office->tags()->attach($attributes["tags"]);
            }

            return $office;
        });

        return OfficeResource::make(
            $office->load(["images", "tags", "user"])
        );
    }

    public function update(Office $office, OfficeRequest $request)
    {
        abort_unless(auth()->user()->tokenCan("office.update"),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize("update", $office);

        $attributes = $request->validated();

        DB::transaction(function () use($attributes, $office) {
            $office->update(
                Arr::except($attributes, "tags")
            );

            if (isset($attributes["tags"])) {
                $office->tags()->sync($attributes["tags"]);
            }

            return $office;
        });

        return OfficeResource::make(
            $office->load("images", "tags", "user")
        );
    }
}
