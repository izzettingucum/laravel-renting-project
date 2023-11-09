<?php

namespace App\Services\OfficeServices;

use App\DTO\OfficeDTO;
use App\Events\OfficeCreated;
use App\Http\Requests\Offices\OfficeListRequest;
use App\Models\Office;
use App\Models\Reservation;
use App\Notifications\Offices\OfficeUpdatedNotification;
use App\Repositories\OfficeRepositories\OfficesRepository;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeService
{
    use AuthorizesRequests;

    protected $officesRepository, $officeDTO, $userRepository;

    public function __construct(OfficesRepository $officesRepository, OfficeDTO $officeDTO, UserRepository $userRepository)
    {
        $this->officesRepository = $officesRepository;
        $this->officeDTO = $officeDTO;
        $this->userRepository = $userRepository;
    }

    public function index(OfficeListRequest $request)
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

    public function show($id)
    {
        $office = $this->findOfficeById($id);

        return $office;
    }

    public function createOffice(array $attributes)
    {
        $attributes["approval_status"] = Office::APPROVAL_PENDING;
        $attributes["userId"] = auth()->id();

        $office = DB::transaction(function () use ($attributes) {
            $office = $this->officesRepository->createOffice(
                $this->officeDTO->create((Arr::except($attributes, "tags")))
            );

            if (isset($attributes["tags"])) {
                $this->officeDTO->tags = $attributes["tags"];
                $this->officeDTO->id = $office->id;
                $this->officesRepository->attachTags($this->officeDTO);
            }

            return $office;
        });

        event(new OfficeCreated($office));

        return $office;
    }

    public function updateOffice(Office $office, array $attributes): Office
    {
        $this->authorize("update", $office);

        $office->fill(Arr::except($attributes, "tags"));

        if ($requiresReview = $office->isDirty(["lat", "lng", "price_per_day", "address_line1"])) {
            $attributes["approval_status"] = Office::APPROVAL_PENDING;
        }

        DB::transaction(function () use ($attributes, $office) {
            $officeDTO = $this->officeDTO->create(Arr::except($attributes, "tags"));
            $officeDTO->setId($office->id);

            $this->officesRepository->update($officeDTO);

            if (isset($attributes["tags"])) {
                $officeDTO->setTags($attributes["tags"]);
                $this->officesRepository->syncTags($officeDTO);
            }

            return $office;
        });

        $admins = $this->userRepository->getAllAdmins();

        if ($requiresReview) {
            Notification::send($admins, new OfficeUpdatedNotification($office));
        }

        return $office;
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

    public function findOfficeById($id)
    {
        $this->officeDTO->setId($id);

        $office = $this->officesRepository->findById($this->officeDTO);

        return $office;
    }
}
