<?php

namespace Kurama\Models;

use Kurama\Core\Model;

class Author extends Model
{
    protected string $table = 'authors';
    
    protected array $fillable = [
        'name', 'email', 'bio', 'avatar', 
        'website', 'twitter', 'github'
    ];
    
    protected array $hidden = ['email'];
}