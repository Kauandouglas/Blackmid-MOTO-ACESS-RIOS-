<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'category',
        'read_time',
        'published_at',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'active' => 'boolean',
        ];
    }
}
