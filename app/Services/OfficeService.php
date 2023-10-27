<?php

namespace App\Services;

use App\Http\DTO\OfficeDTO;
use App\Http\Requests\Offices\CrudRequest;
use App\Http\Requests\Offices\OfficeListRequest;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\Offices\OfficePendingApproval;
use App\Repositories\OfficesRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeService
{
    use AuthorizesRequests;

    protected $officesRepository, $officeDTO;

    public function __construct(OfficesRepository $officesRepository, OfficeDTO $officeDTO)
    {
        $this->officesRepository = $officesRepository;
        $this->officeDTO = $officeDTO;
    }

    public function index(OfficeListRequest $request)
    {

        $officeDTO = new $this->officeDTO([
            "userId" => $request->user_id,
            "visitorId" => $request->visitor_id,
            "lat" => $request->lat,
            "lng" => $request->lng,
            "tags" => $request->tags,
            "perPage" => 20
        ]);

        $offices = $this->officesRepository->getOffices($officeDTO);

        return $offices;
    }

    public function show($id)
    {
        $officeDTO = new $this->officeDTO([
            "id" => $id
        ]);

        $office = $this->officesRepository->findById($officeDTO);

        return $office;
    }

    public function create(CrudRequest $request)
    {
        abort_unless(auth()->user()->tokenCan("office.create"),
            Response::HTTP_FORBIDDEN
        );

        $attributes = $request->validated();
        $attributes["approval_status"] = Office::APPROVAL_PENDING;
        $attributes["userId"] = auth()->id();

        $office = DB::transaction(function () use ($attributes) {
            $office = $this->officesRepository->create(
                new $this->officeDTO((Arr::except($attributes, "tags")))
            );

            if (isset($attributes["tags"])) {
                $this->officeDTO->tags = $attributes["tags"];
                $this->officeDTO->id = $office->id;
                $this->officesRepository->attachTags($this->officeDTO);
            }

            return $office;
        });

        Notification::send(User::where("is_admin", true)->get(), new OfficePendingApproval($office));

        return $office;
    }

    public function update($id, CrudRequest $request)
    {
        abort_unless(auth()->user()->tokenCan("office.update"),
            Response::HTTP_FORBIDDEN
        );

        $this->officeDTO->id = $id;

        $office = $this->officesRepository->findById($this->officeDTO);

        $this->authorize("update", $office);

        $attributes = $request->validated();

        $office->fill(Arr::except($attributes, "tags"));

        if ($requiresReview = $office->isDirty(["lat", "lng", "price_per_day", "address_line1"])) {
            $attributes["approval_status"] = Office::APPROVAL_PENDING;
        }

        DB::transaction(function () use ($attributes, $office) {
            $this->officesRepository->update($this->officeDTO->fill(Arr::except($attributes, "tags")));

            if (isset($attributes["tags"])) {
                $this->officesRepository->syncTags($this->officeDTO->fill(["tags" => $attributes["tags"]]));
            }

            return $office;
        });

        if ($requiresReview) {
            Notification::send(User::where("is_admin", true)->get(), new OfficePendingApproval($office));
        }

        return $office;
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->tokenCan("office.delete"),
            Response::HTTP_FORBIDDEN
        );

        $this->officeDTO->id = $id;

        $office = $this->officesRepository->findById($this->officeDTO);

        $this->authorize("delete", $office);

        throw_if(
            $office->reservations()->where("status", Reservation::STATUS_ACTIVE)->exists(),
            ValidationException::withMessages(["error" => "Cannot delete this office!"])
        );

        $office->images()->each(function ($image) {
            Storage::delete($image->path);
            $image->delete();
        });

        $this->officesRepository->delete($this->officeDTO);
    }
}
