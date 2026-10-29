<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'genre',
        'duration',
        'cover',
        'audio_url',
    ];

    /**
     * Users who favorited this song.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Users who added this song to their playlist.
     */
    public function playlistItems(): HasMany
    {
        return $this->hasMany(PlaylistItem::class);
    }
}
