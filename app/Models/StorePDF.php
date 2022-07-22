<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePDF extends Model
{
    use HasFactory;

    protected $table = 'table_pdf_value';

    protected $fillable = ['pdf'];
}
