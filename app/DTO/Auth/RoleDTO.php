<?php

namespace App\DTO\Auth;

use App\Traits\StaticCreateSelf;

class RoleDTO
{
    use StaticCreateSelf;

    public $id, $role;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }
}
