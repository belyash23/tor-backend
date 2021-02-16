<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $table = 'direction';
    protected $fillable = ['name', 'icon', 'description', 'color', 'status'];
    public $timestamps = false;

    public function images() {
        return $this->hasMany(DirectionImage::class);
    }
    public function keywords() {
        return $this->hasMany(DirectionKeyword::class);
    }
}
