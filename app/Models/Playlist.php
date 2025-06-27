<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use App\Models\Course;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'image',
    ];

    /**
     * Get the category that owns the playlist.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * Get the courses for the playlist.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
