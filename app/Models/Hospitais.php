<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospitais extends Model
{
    protected $table = 'hospitais';

    protected $fillable = ['name', 'id_api'];
}
