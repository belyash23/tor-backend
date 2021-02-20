<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DirectionKeyword
 *
 * @property int $id
 * @property string $word
 * @property int $direction_id
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionKeyword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionKeyword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionKeyword query()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionKeyword whereDirectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionKeyword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectionKeyword whereWord($value)
 * @mixin \Eloquent
 */
class DirectionKeyword extends Model
{
    use HasFactory;

    protected $table = 'keyword';
    protected $hidden = ['id', 'direction_id'];
    protected $fillable = ['word'];
    public $timestamps = false;
}
