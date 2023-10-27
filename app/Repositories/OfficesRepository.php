<?php

namespace App\Repositories;

use App\Http\DTO\OfficeDTO;
use App\Models\Office;
use App\Models\Reservation;
use App\Repositories\Interfaces\OfficesInterface;
use Illuminate\Support\Arr;

class OfficesRepository implements OfficesInterface
{
    protected $officeModel;

    public function __construct(Office $officeModel)
    {
        $this->officeModel = $officeModel;
    }

    public function getOffices(OfficeDTO $officeDTO)
    {
        $query = $this->officeModel->query();

        $query = $this->applyFilters($query, $officeDTO->userId, $officeDTO->visitorId, $officeDTO->lat, $officeDTO->lng, $officeDTO->tags);

        $query = $query
            ->latest("id")
            ->with(["images", "tags", "user"])
            ->withCount(["reservations" => function ($query) {
                return $query->where("status", "=", Reservation::STATUS_ACTIVE);
            }])
            ->paginate($officeDTO->perPage);

        return $query;
    }

    protected function applyFilters($query, $userId, $visitorId, $lat, $lng, $tags)
    {
        return $query
            ->when($userId && auth()->user() && $userId == auth()->id(), function ($query) {
                return $query;
            }, function () {
                return $this->officeModel->FilterByApplyApprovalAndNonHidden();
            })
            ->when($userId, function () use ($userId) {
                return $this->officeModel->FilterByUserId($userId);
            })
            ->when($visitorId, function () use ($visitorId) {
                return $this->officeModel->FilterByVisitorId($visitorId);
            })
            ->when($lat && $lng, function () use ($lat, $lng) {
                return $this->officeModel->FilterByDistance($lat, $lng);
            })
            ->when($tags, function () use ($tags) {
                return $this->officeModel->FilterByTags($tags);
            });
    }

    public function findById(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->findOrFail($officeDTO->id)
            ->withCount(["reservations" => function ($query) {
                return $query->where("status", "=", Reservation::STATUS_ACTIVE);
            }])
            ->with(["images", "tags", "user"])
            ->first();

        return $office;
    }

    public function create(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->create([
            "title" => $officeDTO->title,
            "description" => $officeDTO->description,
            "address_line1" => $officeDTO->address_line1,
            "user_id" => $officeDTO->userId,
            "price_per_day" => $officeDTO->price_per_day,
            "monthly_discount" => $officeDTO->monthly_discount,
            "lat" => $officeDTO->lat,
            "lng" => $officeDTO->lng,
            "approval_status" => $officeDTO->approval_status
        ]);

        return $office->load(["images", "user"]);
    }

    public function update(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->findOrFail($officeDTO->id);

        foreach ($officeDTO as $key => $value) {
            if (!is_null($value)) {
                $office->{$key} = $value;
            }
        }

        $office->save();

        return $office->load(["images", "user"]);
    }

    public function delete(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->findOrFail($officeDTO->id);

        return $office->delete();
    }

    public function attachTags(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->findOrFail($officeDTO->id);

        $office->tags()->attach($officeDTO->tags);

        return $office->load(["tags"]);
    }

    public function syncTags(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->findOrFail($officeDTO->id);

        $tagsToSync = [];

        foreach ($officeDTO->tags as $tag) {
            if (!is_null($tag)) {
                $tagsToSync[] = $tag;
            }
        }

        $office->tags()->sync($tagsToSync);

        return $office->load(["tags"]);
    }
}
