<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectionKeyword extends Model
{
    use HasFactory;

    protected $table = 'keyword';
    protected $hidden = ['id', 'direction_id'];
    public $timestamps = false;
}
