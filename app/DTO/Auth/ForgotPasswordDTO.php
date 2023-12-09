<?php

namespace App\DTO\Auth;

use App\Traits\StaticCreateSelf;

class ForgotPasswordDTO
{
    use StaticCreateSelf;

    public $userId, $code, $rememberToken, $expiredAt;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    public function setRememberToken($rememberToken)
    {
        $this->rememberToken = $rememberToken;
    }

    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    public function setExpiredAt($expiredAt)
    {
        $this->expiredAt = $expiredAt;
    }
}
