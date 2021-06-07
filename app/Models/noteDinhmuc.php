<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class noteDinhmuc extends Model
{
    use HasFactory;
    protected $fillable = ['maDinhMuc','tenMaDinhMuc','ghiChuDinhMuc'];

}
