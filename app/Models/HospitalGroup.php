<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalGroup extends Model
{
    protected $table = 'hospital_group';

    protected $fillable = ['id_group', 'id_hospital'];
}
