<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class approve_note_norm extends Model
{
    use HasFactory;
    protected $fillable = ['maDinhMuc','tenMaDinhMuc','ghiChuDinhMuc','donVi_VI'
    ,'tenCv_EN','donVi_EN','url','created_at','updated_at'];
}
