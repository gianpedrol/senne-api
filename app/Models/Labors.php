<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labors extends Model
{
    protected $table = 'labors';

    protected $fillable = ['name'];
}
