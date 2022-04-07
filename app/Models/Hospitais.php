<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\TextUI\XmlConfiguration\Group;


class Hospitais extends Model
{
    protected $table = 'hospitais';

    protected $fillable = ['name', 'email', 'cnpj', 'image', 'phone', 'grupo_id'];

    public function group()
    {
        return $this->belongsTo(Groups::class, 'id');
    }

    public function users_hospitals()
    {
        return $this->hasMany(UsersHospitals::class, 'id_hospital');
    }
}
