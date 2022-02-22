<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersLabors extends Model
{
    protected $table = 'users_labors';

    protected $fillable = ['id_user','id_labor','type'];
}
