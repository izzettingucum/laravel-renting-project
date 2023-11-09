<?php

namespace App\DTO\Auth;

use App\Traits\StaticCreateSelf;

class UserDTO
{
    use StaticCreateSelf;

    public $id, $name, $email, $password, $roleId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getRoleId()
    {
        return $this->roleId;
    }

    public function setRoleId($value)
    {
        $this->roleId = $value;
    }
}
