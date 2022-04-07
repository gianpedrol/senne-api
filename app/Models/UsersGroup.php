<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersGroup extends Model
{
    protected $table = 'users_groups';

    protected $fillable = ['id_user', 'id_group', 'id_permissao'];

    public function group()
    {
        return $this->belongsTo(Groups::class, 'id');
    }

    public function users_group()
    {
        return $this->hasMany(User::class, 'id', 'id_user');
    }
}
