<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';
    protected $fillable = ['name', 'icon', 'description', 'color', 'status'];
    public $timestamps = false;

    public static function boot() {
        parent::boot();

        static::deleting(function($category) {
            $icon = $category->icon;
            if($icon) unlink(public_path($icon));
        });
    }
}
