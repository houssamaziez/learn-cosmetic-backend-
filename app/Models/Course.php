<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Playlist;

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
}
