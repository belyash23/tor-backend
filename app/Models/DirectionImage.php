<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectionImage extends Model
{
    use HasFactory;

    protected $table = 'direction_image';
    protected $hidden = ['id', 'direction_id'];
    public $timestamps = false;
}
