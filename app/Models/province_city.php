<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class province_city extends Model
{
    use HasFactory;
    protected $fillable = ['name_province','symbol_province'];
}
