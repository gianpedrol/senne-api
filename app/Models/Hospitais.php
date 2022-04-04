<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\TextUI\XmlConfiguration\Group;


class Hospitais extends Model
{
    protected $table = 'hospitais';

    protected $fillable = ['name', 'id_api', 'grupo_id'];

    public function group()
    {
        return $this->belongsTo(Groups::class, 'id');
    }
}
