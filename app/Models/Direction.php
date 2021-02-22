<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Direction
 *
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $description
 * @property string|null $color
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DirectionImage[] $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DirectionKeyword[] $keywords
 * @property-read int|null $keywords_count
 * @method static \Illuminate\Database\Eloquent\Builder|Direction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Direction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Direction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Direction whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Direction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Direction whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Direction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Direction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Direction whereStatus($value)
 * @mixin \Eloquent
 */
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

    public static function boot() {
        parent::boot();

        static::deleting(function($direction) {
            $image = $direction->images();
            if(!$image->get()->isEmpty()) unlink(public_path($image->pluck('src')->first()));

            $icon = $direction->icon;
            unlink(public_path($icon));

            $direction->images()->delete();
            $direction->keywords()->delete();
        });
    }

}
