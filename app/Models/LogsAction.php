<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogsAction extends Model
{
    use HasFactory;

    public function logs_user()
    {
        return $this->hasMany(User::class, 'id');
    }
    public function id_logs()
    {
        return $this->hasMany(LogsAction::class, 'id_log');
    }
}
