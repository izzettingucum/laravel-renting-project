<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerificationMail extends Model
{
    use HasFactory;

    const VERIFY_PENDING = 0;
    const VERIFY_APPROVED = 1;

    protected $casts = [
        "id" => "integer",
        "user_id" => "integer",
        "approval_status" => "boolean"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
