<?php

namespace Kurama\Models;

use Kurama\Core\Model;

class Article extends Model
{
    protected string $table = 'articles';
    
    protected array $fillable = [
        'title', 'slug', 'content', 'excerpt', 
        'category_id', 'author_id', 'status', 
        'featured_image', 'published_at'
    ];
    
    protected array $hidden = [];
}