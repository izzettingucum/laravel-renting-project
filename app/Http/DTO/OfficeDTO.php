<?php

namespace App\Http\DTO;

class OfficeDTO
{
    public $title;
    public $description;
    public $address_line1;
    public $address_line2;
    public $price_per_day;
    public $monthly_discount;
    public $id;
    public $userId;
    public $visitorId;
    public $lat;
    public $lng;
    public $tags;
    public $featured_image_id;
    public $approval_status;
    public $perPage;

    public function __construct(array $data)
    {
        $properties = get_object_vars($this);

        foreach ($properties as $property => $value) {
            if (array_key_exists($property, $data)) {
                $this->{$property} = $data[$property] ?? null;
            }
        }
    }

    public function fill($data)
    {
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->address_line1 = $data['address_line1'] ?? null;
        $this->address_line2 = $data['address_line2'] ?? null;
        $this->price_per_day = $data['price_per_day'] ?? null;
        $this->monthly_discount = $data['monthly_discount'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->lat = $data['lat'] ?? null;
        $this->lng = $data['lng'] ?? null;
        $this->featured_image_id = $data["featured_image_id"] ?? null;
        $this->tags = $data['tags'] ?? null;
        $this->approval_status = $data["approval_status"] ?? null;

        return $this;
    }
}
