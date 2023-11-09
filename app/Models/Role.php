<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    const ROLE_USER = 0;
    const ROLE_ADMIN = 1;

    use HasFactory;

    public function permissions() : BelongsToMany
    {
        return $this->belongsToMany(Permission::class, "role_permissions");
    }
}
