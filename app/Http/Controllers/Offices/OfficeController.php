<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Http\Requests\Offices\CreateRequest;
use App\Http\Requests\Offices\OfficeListRequest;
use App\Http\Requests\Offices\UpdateRequest;
use App\Http\Resources\OfficeResource;
use App\Services\OfficeServices\OfficeService;
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

    public function create(CreateRequest $request) : OfficeResource
    {
        $office = $this->officeService->createOffice($request->toArray());

        return OfficeResource::make(
            $office
        );
    }

    public function update($id, UpdateRequest $request): OfficeResource
    {
        $office = $this->officeService->findOfficeById($id);
        $updateOffice = $this->officeService->updateOffice($office, $request->toArray());

        return OfficeResource::make(
            $updateOffice
        );
    }

    public function delete($id)
    {
        $office = $this->officeService->findOfficeById($id);
        $this->officeService->delete($office);
    }
}
