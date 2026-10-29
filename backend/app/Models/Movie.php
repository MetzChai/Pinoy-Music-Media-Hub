<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_title',
        'soundtrack_title',
        'artist',
        'description',
        'cover_image',
        'youtube_url',
        'youtube_id',
    ];

    /**
     * Users who favorited this movie soundtrack.
     */
    public function movieFavorites(): HasMany
    {
        return $this->hasMany(MovieFavorite::class);
    }
}
