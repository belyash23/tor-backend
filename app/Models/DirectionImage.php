<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DirectionImage
 *
 * @property int $id
 * @property string $src
 * @property int $direction_id
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionImage whereDirectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionImage whereSrc($value)
 * @mixin \Eloquent
 */
class DirectionImage extends Model
{
    use HasFactory;

    protected $table = 'direction_image';
    protected $hidden = ['id', 'direction_id'];
    protected $fillable = ['src'];
    public $timestamps = false;
}
