<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainHospital extends Model
{
    use HasFactory;

    public function hospitals()
    {
        return $this->hasMany(Hospitais::class, 'codprocedencia');
    }
}
