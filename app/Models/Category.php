<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
