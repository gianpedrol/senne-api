<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersGroup extends Model
{
    protected $table = 'users_groups';

    protected $fillable = ['id_user', 'id_group', 'id_permissao'];
}
