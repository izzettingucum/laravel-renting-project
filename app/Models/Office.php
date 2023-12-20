<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    const APPROVAL_PENDING = 1;
    const APPROVAL_APPROVED = 2;

    protected $casts = [
        "lat" => "float",
        "lng" => "float",
        "approval_status" => "integer",
        "featured_image_id" => "integer",
        "hidden" => "bool"
    ];

    protected $fillable = [
        "lat",
        "lng",
        "featured_image_id",
        "approval_status",
        "hidden"
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservations() : HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function officeInfo()
    {
        return $this->hasOne(OfficeInfo::class);
    }

    public function images() : MorphMany
    {
        return $this->morphMany(Image::class, "resource");
    }

    public function featuredImage() : BelongsTo
    {
        return $this->belongsTo(Image::class, "featured_image_id", "id");
    }

    public function tags() : BelongsToMany
    {
        return $this->belongsToMany(Tag::class, "offices_tags");
    }

    public function scopeNearestTo($query, $lat, $lng)
    {
        return $query
            ->select()
            ->orderByRaw(
                'POW(69.1 * (lat - ?), 2) + POW(69.1 * (? - lng) * COS(lat / 57.3), 2)',
                [$lat, $lng]
            );
    }

    public function scopeFilterByApplyApprovalAndNonHidden($query)
    {
        return $query->where("approval_status", Office::APPROVAL_APPROVED)
            ->where("hidden", false);
    }

    public function scopeFilterByUserId($query, $userId)
    {
        return $query->where("user_id", $userId);
    }

    public function scopeFilterByVisitorId($query, $visitorId)
    {
        return $query->whereRelation("reservations", "user_id", "=", $visitorId);
    }

    public function scopeFilterByDistance($query, $lat, $lng)
    {
        return $query->nearestTo($lat, $lng);
    }

    public function scopeFilterByTags($query, $tags)
    {
        $query->whereHas("tags", function ($query) use ($tags) {
            $query->whereIn("tags.name", $tags);
        }, "=", count($tags));

        return $query;
    }

}
