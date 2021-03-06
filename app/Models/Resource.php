<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $table = 'resource';
    protected $fillable = ['name', 'short_description', 'description', 'date_start', 'date_end', 'age_min', 'age_max', 'location', 'website', 'image', 'status', 'category_id'];
    public $timestamps = false;

    public function directions() {
        return $this->belongsToMany(Direction::class, 'resource_direction', 'resource_id', 'direction_id');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($resource) {
            $image = $resource->image;
            if($image && stripos($image, 'resource-')) unlink(public_path($image));

            $resource->directions()->detach();
        });
    }
}
