<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'listing_code',
        'user_id',
        'title',
        'category_id',
        'category_name',
        'condition_id',
        'condition_name',
        'price',
        'currency',
        'location_name',
        'latitude',
        'longitude',
        'description',
        'status',
    ];

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

