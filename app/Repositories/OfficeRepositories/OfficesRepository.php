<?php

namespace App\Repositories\OfficeRepositories;

use App\DTO\OfficeDTO;
use App\Models\Office;
use App\Models\OfficeInfo;
use App\Models\Reservation;
use App\Repositories\Interfaces\OfficesInterface;

class OfficesRepository implements OfficesInterface
{
    protected $officeModel, $officeInfoModel;

    public function __construct(Office $officeModel, OfficeInfo $officeInfo)
    {
        $this->officeModel = $officeModel;
        $this->officeInfoModel = $officeInfo;
    }

    public function getOffices(OfficeDTO $officeDTO)
    {
        $query = $this->officeModel->query();

        $query = $this->applyFilters($query, $officeDTO->userId, $officeDTO->visitorId, $officeDTO->lat, $officeDTO->lng, $officeDTO->tags);

        $query = $query
            ->latest("id")
            ->with(["officeInfo", "images", "tags", "user"])
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
            ->with(["officeInfo", "images", "tags", "user"])
            ->first();

        return $office;
    }

    public function createOffice(OfficeDTO $officeDTO)
    {
        $office = $this->officeModel->create([
            "user_id" => $officeDTO->userId,
            "lat" => $officeDTO->lat,
            "lng" => $officeDTO->lng,
            "approval_status" => $officeDTO->approval_status
        ]);

        return $office;
    }

    public function createOfficeInfo(Office $office, OfficeDTO $officeDTO)
    {
        $office = $office->officeInfo()->create([
            "title" => $officeDTO->title,
            "description" => $officeDTO->description,
            "address_line1" => $officeDTO->address_line1,
            "price_per_day" => $officeDTO->price_per_day,
            "monthly_discount" => $officeDTO->monthly_discount
        ]);

        return $office;
    }

    public function updateOffice(Office $office, OfficeDTO $officeDTO)
    {
        foreach ($officeDTO as $key => $value) {
            if (!is_null($value) && in_array($key, $office->getFillable())) {
                $office->{$key} = $value;
            }
        }

        $office->save();

        return $office;
    }


    public function updateOfficeInfo(OfficeInfo $office, OfficeDTO $officeDTO)
    {
        foreach ($officeDTO as $key => $value) {
            if (!is_null($value) && in_array($key, $office->getFillable())) {
                $office->{$key} = $value;
            }
        }

        $office->save();

        return $office;
    }

    public function getOfficeInfo(Office $office)
    {
        return $office->officeInfo()->first();
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

    public function syncTags(Office $office, OfficeDTO $officeDTO)
    {
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
