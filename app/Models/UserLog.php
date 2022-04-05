<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;
    protected $table = 'logs_user';

    public function logs_user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
