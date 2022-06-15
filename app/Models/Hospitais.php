<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\TextUI\XmlConfiguration\Group;


class Hospitais extends Model
{
    protected $table = 'hospitais';

    protected $fillable = ['name', 'email', 'cnpj', 'image', 'phone', 'grupo_id', 'uuid', 'codprocedencia'];

    public function group()
    {
        return $this->hasOne(Groups::class, 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id');
    }
    public function users_hospital()
    {
        return $this->hasMany(UsersHospitals::class, 'id_user');
    }
    public function domainHospital()
    {
        return $this->hasMany(DomainHospital::class, 'codprocedencia');
    }
}
