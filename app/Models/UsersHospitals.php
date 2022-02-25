<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersHospitals extends Model
{
    protected $table = 'users_hospitals';

    protected $fillable = ['id_user','id_labor','type'];
}