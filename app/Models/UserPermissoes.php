<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermissoes extends Model
{
    protected $table = 'permissoes';

    protected $fillable = ['id_user','id_permissao'];
}
