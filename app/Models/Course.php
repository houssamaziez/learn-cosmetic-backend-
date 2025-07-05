<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Playlist;
use App\Models\Like;
use App\Models\Comment;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'playlist_id',
        'image_path',
        'video_path',
        'video_duration',
        'is_watched',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
