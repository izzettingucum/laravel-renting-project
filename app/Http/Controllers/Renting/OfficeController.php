<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $offices = Office::query()
            ->when(request("user_id") && auth()->user() && request("user_id") == auth()->id(),
            function ($query) {
                return $query;
                },
            function ($query) {
                return $query->where("approval_status", Office::APPROVAL_APPROVED)
                    ->where("hidden", false);
            })
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

        Notification::send(User::where("is_admin", true)->get(), new OfficePendingApproval($office));

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

        $office->fill(Arr::except($attributes, "tags"));

        if($requiresReview = $office->isDirty(["lat", "lng", "price_per_day", "address_line1"])) {
            $office->fill(["approval_status" => Office::APPROVAL_PENDING]);
        }

        DB::transaction(function () use($attributes, $office) {
            $office->save();

            if (isset($attributes["tags"])) {
                $office->tags()->sync($attributes["tags"]);
            }

            return $office;
        });

        if($requiresReview) {
            Notification::send(User::where("is_admin", true)->get(), new OfficePendingApproval($office));
        }

        return OfficeResource::make(
            $office->load("images", "tags", "user")
        );
    }

    public function delete(Office $office)
    {
        abort_unless(auth()->user()->tokenCan("office.delete"),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize("delete", $office);

        throw_if(
            $office->reservations()->where("status", Reservation::STATUS_ACTIVE)->exists(),
            ValidationException::withMessages(["error" => "Cannot delete this office!"])
        );

        $office->images()->each(function ($image) {
            Storage::delete($image->path);
            $image->delete();
        });

        $office->delete();
    }
}
