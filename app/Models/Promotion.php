<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'start_date',
        'end_date',
        'is_active',
        'playlist_id',
    ];
    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
}
