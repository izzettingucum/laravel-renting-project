<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        "title" => "string",
        "price_per_day" => "integer",
        "monthly_discount" => "integer",
        "featured_image_id" => "integer"
    ];

    protected $fillable = [
        "title",
        "description",
        "address_line1",
        "address_line2",
        "price_per_day",
        "monthly_discount"
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
