<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Hospitais;

class Groups extends Model
{
    protected $table = 'groups';

    protected $fillable = ['name', 'cnpj', 'image', 'phone'];

    public function hospitals()
    {
        return $this->hasMany(Hospitais::class, 'grupo_id');
    }

    public function usersGroup()
    {
        return $this->hasMany(UsersGroup::class, 'id_group');
    }
}
