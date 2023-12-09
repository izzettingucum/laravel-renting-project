<?php

namespace App\DTO;

use App\Traits\StaticCreateSelf;

class TwoFactorDTO
{
    use StaticCreateSelf;

    public $id, $userId, $code, $expiresAt;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = $value;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($value)
    {
        return $this->code = $value;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function setExpiresAt($value)
    {
        $this->expiresAt = $value;
    }
}
