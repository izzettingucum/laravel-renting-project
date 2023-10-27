<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Http\Requests\Offices\CrudRequest;
use App\Http\Requests\Offices\OfficeListRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\Offices\OfficePendingApproval;
use App\Repositories\OfficesRepository;
use App\Services\OfficeService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfficeController extends Controller
{
    protected $officeService;

    public function __construct(OfficeService $officeService)
    {
        $this->officeService = $officeService;
    }

    public function index(OfficeListRequest $request): AnonymousResourceCollection
    {
        $offices = $this->officeService->index($request);

        return OfficeResource::collection(
            $offices
        );
    }

    public function show($id): OfficeResource
    {
        $office = $this->officeService->show($id);

        return OfficeResource::make(
            $office
        );
    }

    public function create(CrudRequest $request) : OfficeResource
    {
        $office = $this->officeService->create($request);

        return OfficeResource::make(
            $office->load(["images", "tags", "user"])
        );
    }

    public function update($id, CrudRequest $request)
    {
        $office = $this->officeService->update($id, $request);

        return OfficeResource::make(
            $office->load("images", "tags", "user")
        );
    }

    public function delete($id)
    {
        $this->officeService->delete($id);
    }
}
