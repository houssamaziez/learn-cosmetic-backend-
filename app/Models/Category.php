<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Playlist;

class Category extends Model
{
    // Allow mass assignment for these fields
    protected $fillable = [
        'name',
        'description',
        'icon',
    ];

    // Enable timestamps if your table has 'created_at' and 'updated_at' columns
    public $timestamps = true;
    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }
}
