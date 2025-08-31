<?php

namespace Kurama\Models;

use Kurama\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name', 'email', 'password', 'role'
    ];
    
    protected array $hidden = ['password'];
}