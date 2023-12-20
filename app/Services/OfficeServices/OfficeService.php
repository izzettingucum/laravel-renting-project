<?php

namespace App\Services\OfficeServices;

use App\DTO\OfficeDTO;
use App\Events\OfficeCreated;
use App\Http\Requests\Offices\OfficeListRequest;
use App\Models\Office;
use App\Models\OfficeInfo;
use App\Models\Reservation;
use App\Notifications\Offices\OfficeUpdatedNotification;
use App\Repositories\Interfaces\OfficesInterface;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    protected $officesRepository, $officeDTO, $userRepository;

    public function __construct(OfficesInterface $officesRepository, OfficeDTO $officeDTO, UserRepository $userRepository)
    {
        $this->officesRepository = $officesRepository;
        $this->officeDTO = $officeDTO;
        $this->userRepository = $userRepository;
    }

    public function getOffices(OfficeListRequest $request)
    {
        $officeDTO = $this->officeDTO->create([
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

    public function findOfficeById($id)
    {
        $this->officeDTO->setId($id);

        try {
            $office = $this->officesRepository->findById($this->officeDTO);
        } catch (ModelNotFoundException $e) {
            throw ValidationException::withMessages([
                "office_id" => "Invalid office_id"
            ])->status(Response::HTTP_NOT_FOUND);
        }


        return $office;
    }

    public function createOffice(array $attributes)
    {
        $attributes["approval_status"] = Office::APPROVAL_PENDING;
        $attributes["userId"] = auth()->id();

        $office = DB::transaction(function () use ($attributes) {
            $officeDTO = $this->officeDTO->create((Arr::except($attributes, "tags")));
            $office = $this->officesRepository->createOffice($officeDTO);

            $this->officesRepository->createOfficeInfo($office, $officeDTO);

            if (isset($attributes["tags"])) {
                $officeDTO->tags = $attributes["tags"];
                $officeDTO->id = $office->id;
                $this->officesRepository->attachTags($officeDTO);
            }

            return $office->load(["officeInfo", "images", "tags"]);
        });

        return $office;
    }

    public function updateOffice(Office $office, array $attributes): Office
    {
        $this->authorize("update", $office);

        $officeInfo = $this->officesRepository->getOfficeInfo($office);

        $office = $this->fillOfficeAttributes($office, $attributes);

        $officeInfo = $this->fillOfficeInfoAttributes($officeInfo, $attributes);

        $requiresReview = $this->checkReviewRequirements($office, $officeInfo);

        if ($requiresReview) {
            $attributes["approval_status"] = Office::APPROVAL_PENDING;
        }

        DB::transaction(function () use ($attributes, $office, $officeInfo, $requiresReview) {
            $officeDTO = $this->officeDTO->create(Arr::except($attributes, "tags"));

            if ($office->isDirty() || $requiresReview) {
                $this->officesRepository->updateOffice($office, $officeDTO);
            }

            if ($officeInfo->isDirty()) {
                $this->officesRepository->updateOfficeInfo($officeInfo, $officeDTO);
            }

            if (isset($attributes["tags"])) {
                $officeDTO->setTags($attributes["tags"]);
                $this->officesRepository->syncTags($office, $officeDTO);
            }

            return $office->load(["officeInfo", "images", "tags"]);
        });

        $admins = $this->userRepository->getAllAdmins();

        if ($requiresReview) {
            Notification::send($admins, new OfficeUpdatedNotification($office));
        }

        return $office;
    }

    private function checkReviewRequirements(Office $office, OfficeInfo $officeInfo): bool
    {
        return $office->isDirty(["lat", "lng"]) || $officeInfo->isDirty(["price_per_day", "address_line1"]);
    }

    private function fillOfficeAttributes(Office $office, array $attributes) : Office
    {
        $office->fill(Arr::only($attributes, $office->getFillable()));

        return $office;
    }

    private function fillOfficeInfoAttributes(OfficeInfo $officeInfo, array $attributes) : OfficeInfo
    {
        $officeInfo->fill(Arr::only($attributes, $officeInfo->getFillable()));

        return $officeInfo;
    }

    public function delete(Office $office)
    {
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

    public function triggerOfficeCreatedEvent(Office $office)
    {
        event(new OfficeCreated($office));
    }
}
