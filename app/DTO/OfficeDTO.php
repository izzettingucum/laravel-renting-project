<?php

namespace App\DTO;

use App\Traits\StaticCreateSelf;

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

    use StaticCreateSelf;

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAddressLine1()
    {
        return $this->address_line1;
    }

    public function getAddressLine2()
    {
        return $this->address_line2;
    }

    public function getPricePerDay()
    {
        return $this->price_per_day;
    }

    public function getMonthlyDiscount()
    {
        return $this->monthly_discount;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getVisitorId()
    {
        return $this->visitorId;
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function getLng()
    {
        return $this->lng;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getFeaturedImageId()
    {
        return $this->featured_image_id;
    }

    public function getApprovalStatus()
    {
        return $this->approval_status;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setAddressLine1($address_line1)
    {
        $this->address_line1 = $address_line1;
    }

    public function setAddressLine2($address_line2)
    {
        $this->address_line2 = $address_line2;
    }

    public function setPricePerDay($price_per_day)
    {
        $this->price_per_day = $price_per_day;
    }

    public function setMonthlyDiscount($monthly_discount)
    {
        $this->monthly_discount = $monthly_discount;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function setVisitorId($visitorId)
    {
        $this->visitorId = $visitorId;
    }

    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function setFeaturedImageId($featured_image_id)
    {
        $this->featured_image_id = $featured_image_id;
    }

    public function setApprovalStatus($approval_status)
    {
        $this->approval_status = $approval_status;
    }

    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }
}
