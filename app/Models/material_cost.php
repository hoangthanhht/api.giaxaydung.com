<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class material_cost extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['maVatTu','tenVatTu','giaVatTu',
    'donVi','nguon','ghiChu','tinh','tacGia','user_id',
    'created_at','updated_at'];
}