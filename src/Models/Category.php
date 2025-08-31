<?php

namespace Kurama\Models;

use Kurama\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';
    
    protected array $fillable = [
        'name', 'slug', 'description', 'parent_id'
    ];
}