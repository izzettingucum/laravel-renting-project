<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;

    protected $casts = [
        "price" => "integer",
        "status" => "integer",
        "start_date" => "immutable_date",
        "end_date" => "immutable_date",
        "wifi_password" => "encrypted"
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office() : BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function scopeFilterByOfficeId($query, $officeId)
    {
        return $query->where("office_id", $officeId);
    }

    public function scopeFilterByUserId($query, $userId)
    {
        return $query->where("user_id", $userId);
    }

    public function scopeFilterByStatus($query, $status)
    {
        return $query->where("status", $status);
    }

    public function scopeFilterByDateRange($query, $fromDate, $toDate)
    {
        return $query->betweenDates($fromDate, $toDate);
    }

    public function scopeActiveBetween($query, $from, $to)
    {
        return $query->whereStatus(Reservation::STATUS_ACTIVE)
            ->betweenDates($from, $to);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query
            ->whereBetween("start_date", [$from, $to])
            ->orWhereBetween("end_date", [$from, $to])
            ->orWhere(function ($query) use ($from, $to) {
                return $query
                    ->where("start_date", "<" , $from)
                    ->where("end_date", ">", $to);
            });
    }
}
