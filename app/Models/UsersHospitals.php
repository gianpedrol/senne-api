<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersHospitals extends Model
{
    protected $table = 'users_hospitals';

    protected $fillable = ['id_user', 'id_hospital', 'id_group', 'type'];

    public function hospital()
    {
        return $this->belongsTo(Hospitais::class, 'id');
    }

    public function usersHospital()
    {
        return $this->hasMany(User::class, 'id');
    }
}
